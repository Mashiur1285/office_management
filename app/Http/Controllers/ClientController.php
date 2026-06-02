<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Models\JobSector;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $bdCompanyStatus = $request->input('bd_company_status');
        $bdCompanyScope = $request->boolean('bd_company_scope');
        $agencyScope = $request->boolean('agency_scope');
        $foreignCountry = $request->input('foreign_country');

        $query = Client::query()
            ->with(['bdCompany', 'foreignCompany', 'agent', 'documentLocation'])
            ->latest();

        if ($agencyScope) {
            $query->where(function ($scope) {
                $scope->doesntHave('documentLocation')
                    ->orWhereHas('documentLocation', function ($q) {
                        $q->whereIn('holder_type', ['agency', 'agency_user'])
                            ->whereNull('returned_at');
                    });
            });
        } elseif ($bdCompanyScope || $bdCompanyStatus) {
            $query->whereHas('documentLocation', function ($q) use ($bdCompanyStatus) {
                $q->where('holder_type', 'bd_company');
                if ($bdCompanyStatus && $bdCompanyStatus !== 'all') {
                    if ($bdCompanyStatus === 'pending') {
                        $q->where(function ($inner) {
                            $inner->whereNull('processing_status')
                                ->orWhere('processing_status', 'pending');
                        });
                    } else {
                        $q->where('processing_status', $bdCompanyStatus);
                    }
                }
            });
        }

        if ($foreignCountry) {
            $query->where(function ($scope) use ($foreignCountry) {
                $scope->where('foreign_company_country', $foreignCountry)
                    ->orWhereHas('foreignCompany', function ($foreignQuery) use ($foreignCountry) {
                        $foreignQuery->where('country', $foreignCountry);
                    });
            });
        }

        $clients = $query
            ->get()
            ->map(function (Client $client) {
                $holder = $client->documentLocation;
                $holderLabelMap = [
                    'agency' => 'Agency',
                    'bd_company' => 'BD Company',
                    'foreign_company' => 'Foreign Company',
                    'agent' => 'Agent',
                    'other' => 'Other',
                ];
                $daysWithHolder = $holder?->received_at ? $holder->received_at->diffInDays(now()) : null;
                $overdue = $daysWithHolder !== null && $daysWithHolder > 8 && empty($holder?->returned_at);

                $status = $this->resolveStatus($holder);

                // Calculate VAT receivable for this client
                $vatReceivable = \App\Models\IncomeEntry::where('client_id', $client->id)
                    ->sum('vat_amount');

                // Calculate unpaid VAT amount
                $vatPaidAmount = (float) $client->vat_paid_amount;
                $vatUnpaid = max(0, $vatReceivable - $vatPaidAmount);

                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'passport_number' => $client->passport_number,
                    'nid_number' => $client->nid_number,
                    'mobile' => $client->mobile,
                    'job_sector' => $client->job_sector,
                    'job_sub_sector' => $client->job_sub_sector,
                    'district' => $client->district,
                    'bd_company' => $client->bdCompany?->name,
                    'foreign_company' => $client->foreignCompany?->name
                        ?? $client->foreign_company_name
                        ?? $client->foreign_company_country,
                    'agent_name' => $client->agent_name ?? $client->agent?->name,
                    'agent' => $client->agent?->name,
                    'status' => $status['label'],
                    'status_value' => $status['value'],
                    'status_badge' => $status['badge'],
                    'current_due' => $client->current_due,
                    'vat_receivable' => (float) $vatReceivable,
                    'vat_paid_amount' => $vatPaidAmount,
                    'vat_unpaid' => $vatUnpaid,
                    'vat_paid' => $client->vat_paid && $vatUnpaid == 0,
                    'vat_chalan_number' => $client->vat_chalan_number,
                    'photo_url' => $client->photo_path ? asset('storage/' . $client->photo_path) : null,
                    'current_holder_type' => $holder?->holder_type,
                    'current_holder_label' => $holder?->holder_type ? ($holderLabelMap[$holder->holder_type] ?? $holder->holder_type) : null,
                    'processing_status' => $holder?->processing_status,
                    'days_with_holder' => $daysWithHolder,
                    'holder_overdue' => $overdue,
                ];
            })
            ->values();

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'filters' => [
                'bd_company_status' => $bdCompanyStatus,
                'bd_company_scope' => $bdCompanyScope,
                'agency_scope' => $agencyScope,
                'foreign_country' => $foreignCountry,
            ],
            'readOnly' => $request->routeIs('database.*'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Clients/Create', [
            ...$this->formData(),
            'client' => null,
            'mode' => 'create',
        ]);
    }

    public function export(Request $request, ?string $type = null)
    {
        $bdCompanyStatus = $request->input('bd_company_status');
        $bdCompanyScope = $request->boolean('bd_company_scope');
        $agencyScope = $request->boolean('agency_scope');
        $foreignCountry = $request->input('foreign_country');

        $query = Client::query()
            ->with(['bdCompany', 'foreignCompany', 'agent', 'documentLocation'])
            ->latest();

        if ($agencyScope) {
            $query->where(function ($scope) {
                $scope->doesntHave('documentLocation')
                    ->orWhereHas('documentLocation', function ($q) {
                        $q->whereIn('holder_type', ['agency', 'agency_user'])
                            ->whereNull('returned_at');
                    });
            });
        } elseif ($bdCompanyScope || $bdCompanyStatus) {
            $query->whereHas('documentLocation', function ($q) use ($bdCompanyStatus) {
                $q->where('holder_type', 'bd_company');
                if ($bdCompanyStatus && $bdCompanyStatus !== 'all') {
                    if ($bdCompanyStatus === 'pending') {
                        $q->where(function ($inner) {
                            $inner->whereNull('processing_status')
                                ->orWhere('processing_status', 'pending');
                        });
                    } else {
                        $q->where('processing_status', $bdCompanyStatus);
                    }
                }
            });
        }

        if ($foreignCountry) {
            $query->where(function ($scope) use ($foreignCountry) {
                $scope->where('foreign_company_country', $foreignCountry)
                    ->orWhereHas('foreignCompany', function ($foreignQuery) use ($foreignCountry) {
                        $foreignQuery->where('country', $foreignCountry);
                    });
            });
        }

        $clients = $query
            ->get()
            ->map(function (Client $client) {
                $holder = $client->documentLocation;
                $status = $this->resolveStatus($holder);

                return [
                    'name' => $client->name,
                    'nid_number' => $client->nid_number,
                    'passport_number' => $client->passport_number,
                    'job_sector' => $client->job_sector,
                    'agent_name' => $client->agent_name ?? $client->agent?->name,
                    'status' => $status['label'],
                    'bd_company' => $client->bdCompany?->name,
                    'foreign_company' => $client->foreignCompany?->name
                        ?? $client->foreign_company_name
                        ?? $client->foreign_company_country,
                    'current_due' => $client->current_due,
                ];
            });

        if ($type === 'pdf') {
            $fileName = 'clients-report-' . now()->format('Y-m-d') . '.pdf';

            return Pdf::loadView('pdfs.client_list_report', [
                'clients' => $clients,
            ])->download($fileName);
        }

        $handle = fopen('php://memory', 'w');

        fputcsv($handle, [
            'Name',
            'NID',
            'Passport',
            'Job Sector',
            'Agent',
            'Status',
            'BD Company',
            'Foreign Company',
            'Due Amount',
        ]);

        foreach ($clients as $client) {
            fputcsv($handle, [
                $client['name'],
                $client['nid_number'],
                $client['passport_number'],
                $client['job_sector'],
                $client['agent_name'],
                $client['status'],
                $client['bd_company'],
                $client['foreign_company'],
                $client['current_due'],
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $fileName = 'clients-report-' . now()->format('Y-m-d') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function edit(Client $client)
    {
        // For old clients without payment records, fall back to calculated paid amount
        $hasPaymentRecords = $client->payments()->exists();
        $totalFee = (float) ($client->total_fee ?? 0);
        $due = (float) ($client->current_due ?? 0);
        $paid = max(0, $totalFee - $due);

        $totalReceived = $hasPaymentRecords
            ? (float) $client->payments()->where('type', 'payment')->sum('amount')
            : $paid;

        $totalRefunded = (float) $client->payments()
            ->where('type', 'refund')
            ->sum('amount');

        $paymentHistory = $client->payments()
            ->with(['agent:id,name', 'creator:id,name'])
            ->latest('payment_date')
            ->latest('id')
            ->get()
            ->map(function ($payment) {
                return [
                    'id'             => $payment->id,
                    'payment_date'   => $payment->payment_date?->format('Y-m-d'),
                    'type'           => $payment->type,
                    'source'         => $payment->source,
                    'agent_name'     => $payment->agent?->name,
                    'amount'         => (float) $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'notes'          => $payment->notes,
                    'created_by'     => $payment->creator?->name,
                ];
            });

        return Inertia::render('Clients/Create', [
            ...$this->formData(),
            'client' => $this->transformClient($client),
            'totalReceived' => (float) $totalReceived,
            'totalRefunded' => (float) $totalRefunded,
            'paymentHistory' => $paymentHistory,
            'mode' => 'edit',
        ]);
    }

    public function show(Request $request, Client $client)
    {
        $client->load(['bdCompany', 'foreignCompany', 'agent', 'documentLocation']);

        $holder = $client->documentLocation;
        $holderLabelMap = [
            'agency' => 'Agency',
            'bd_company' => 'BD Company',
            'foreign_company' => 'Foreign Company',
            'agent' => 'Agent',
            'other' => 'Other',
        ];

        $status = $this->resolveStatus($holder);

        $totalFee = (float) ($client->total_fee ?? 0);
        $due = (float) ($client->current_due ?? 0);
        $paid = max(0, $totalFee - $due);

        // For old clients without payment records, fall back to calculated paid amount
        $hasPaymentRecords = $client->payments()->exists();
        $totalReceived = $hasPaymentRecords
            ? (float) $client->payments()->where('type', 'payment')->sum('amount')
            : $paid;
        $totalRefunded = (float) $client->payments()->where('type', 'refund')->sum('amount');

        $paymentHistory = $client->payments()
            ->with(['agent:id,name', 'creator:id,name'])
            ->latest('payment_date')
            ->latest('id')
            ->get()
            ->map(function ($payment) {
                return [
                    'id'             => $payment->id,
                    'payment_date'   => $payment->payment_date?->format('Y-m-d'),
                    'type'           => $payment->type,
                    'source'         => $payment->source,
                    'agent_name'     => $payment->agent?->name,
                    'amount'         => (float) $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'notes'          => $payment->notes,
                    'created_by'     => $payment->creator?->name,
                ];
            });

        // Build stages dynamically based on document flow
        $stages = [];
        $currentStageIndex = null;

        // Check if documents are currently at BD Company
        $isAtBdCompany = $holder && $holder->holder_type === 'bd_company';

        // Determine BD Company name for stage description
        $bdCompanyName = null;
        if ($client->bdCompany) {
            $bdCompanyName = $client->bdCompany->name;
        } elseif ($isAtBdCompany && $holder->holder_name) {
            $bdCompanyName = $holder->holder_name;
        }

        // Stage 1: Processing at Agency
        $stages[] = [
            'name' => 'Processing at Agency',
            'description' => 'Documents received and being processed by MITT staff',
            'date' => null,
        ];

        // Stage 2: Processing at BD Company (if applicable)
        // Add this stage if client has BD Company OR documents are currently at BD Company
        if ($client->bdCompany || $isAtBdCompany) {
            $stages[] = [
                'name' => "Processing at BD Company",
                'description' => $bdCompanyName ?? 'BD Processing Company',
                'date' => null,
            ];
        }

        // Stage 3: Final Status (Completed or Rejected)
        $stages[] = [
            'name' => 'Final Status',
            'description' => 'Application processing complete',
            'date' => null,
        ];

        // Determine current stage based on holder type and processing status
        // Priority: completed/rejected > at bd_company > at agency

        if ($holder && ($holder->processing_status === 'completed' || $holder->processing_status === 'rejected')) {
            // Final stage - completed or rejected
            $currentStageIndex = count($stages) - 1;
        } elseif ($isAtBdCompany) {
            // File is at BD Company (any status except completed/rejected)
            // BD Company stage is at index 1 (after agency stage at index 0)
            $currentStageIndex = 1;
        } else {
            // File is at agency or other location
            $currentStageIndex = 0;
        }

        $stageIndex = $currentStageIndex;

        // Calculate VAT receivable for this client
        $vatReceivable = \App\Models\IncomeEntry::where('client_id', $client->id)
            ->sum('vat_amount');

        // Calculate unpaid VAT amount
        $vatPaidAmount = (float) $client->vat_paid_amount;
        $vatUnpaid = max(0, $vatReceivable - $vatPaidAmount);

        return Inertia::render('Clients/Show', [
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'passport_number' => $client->passport_number,
                'nid_number' => $client->nid_number,
                'photo_path' => $client->photo_path,
                'nid_file_path' => $client->nid_file_path,
                'passport_file_path' => $client->passport_file_path,
                'medical_report_path' => $client->medical_report_path,
                'fit_report_path' => $client->fit_report_path,
                'fit_status' => $client->fit_status,
                'status' => $status['label'],
                'status_value' => $status['value'],
                'status_badge' => $status['badge'],
                'bd_company' => $client->bdCompany?->name,
                'foreign_company' => $client->foreignCompany?->name,
                'agent_name' => $client->agent?->name ?? $client->agent_name,
                'documents_submitted_to' => $client->documents_submitted_to,
                'documents_submission_phone' => $client->documents_submission_phone,
                'current_holder_label' => $holder?->holder_type ? ($holderLabelMap[$holder->holder_type] ?? $holder->holder_type) : null,
                'current_holder_name' => $holder?->holder_name,
                'processing_status' => $holder?->processing_status,
                'notes' => $client->notes,
                'online_status' => $client->online_status,
                'calling_status' => $client->calling_status,
                'evisa_status' => $client->evisa_status,
                'total_fee' => $totalFee,
                'current_due' => $due,
                'paid_amount' => $paid,
                'vat_receivable' => (float) $vatReceivable,
                'vat_paid_amount' => $vatPaidAmount,
                'vat_unpaid' => $vatUnpaid,
                'vat_paid' => $client->vat_paid && $vatUnpaid == 0,
                'vat_chalan_number' => $client->vat_chalan_number,
                'vat_payment_date' => $client->vat_payment_date?->format('Y-m-d'),
                'total_received' => $totalReceived,
                'total_refunded' => $totalRefunded,
            ],
            'stages' => $stages,
            'currentStageIndex' => $stageIndex === false ? null : $stageIndex,
            'paymentHistory' => $paymentHistory,
            'tickets' => $client->airlineTickets()
                ->orderByDesc('flight_date')
                ->get(['id', 'passenger_name', 'airline_name', 'flight_number', 'origin', 'destination', 'flight_date', 'status', 'original_flight_date'])
                ->map(fn ($t) => [
                    'id'                   => $t->id,
                    'passenger_name'       => $t->passenger_name,
                    'airline_name'         => $t->airline_name,
                    'flight_number'        => $t->flight_number,
                    'origin'               => $t->origin,
                    'destination'          => $t->destination,
                    'flight_date'          => $t->flight_date?->format('Y-m-d'),
                    'original_flight_date' => $t->original_flight_date?->format('Y-m-d'),
                    'status'               => $t->status,
                    'status_badge'         => $t->status_badge_class,
                ]),
            'readOnly' => $request->routeIs('database.*'),
        ]);
    }

    public function store(StoreClientRequest $request)
    {
        $data = $request->validated();

        $fileFields = [
            'photo' => 'photo_path',
            'nid_file' => 'nid_file_path',
            'passport_file' => 'passport_file_path',
            'medical_report' => 'medical_report_path',
            'fit_report' => 'fit_report_path',
        ];

        foreach ($fileFields as $input => $column) {
            if ($request->hasFile($input)) {
                $data[$column] = $request->file($input)->store("clients/{$input}", 'public');
            }
        }

        $paymentSource = $data['payment_source'] ?? 'client';
        $paymentAgentId = $data['payment_agent_id'] ?? null;
        $paidAmount = (float) ($data['partial_paid_amount'] ?? 0);

        $data = collect($data)->except(array_keys($fileFields))->except(['payment_source', 'payment_agent_id'])->toArray();

        $client = Client::create($data);

        if ($paidAmount > 0) {
            $client->payments()->create([
                'type' => 'payment',
                'source' => $paymentSource,
                'agent_id' => $paymentSource === 'agent' ? $paymentAgentId : null,
                'amount' => $paidAmount,
                'payment_date' => $data['partial_payment_date'] ?? now()->toDateString(),
                'created_by' => auth()->id(),
            ]);
        }

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client created successfully.');
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        $data = $request->validated();
        $paymentSource = $data['payment_source'] ?? null;
        $paymentAgentId = $data['payment_agent_id'] ?? null;
        unset($data['payment_source'], $data['payment_agent_id']);

        $fileFields = [
            'photo' => 'photo_path',
            'nid_file' => 'nid_file_path',
            'passport_file' => 'passport_file_path',
            'medical_report' => 'medical_report_path',
            'fit_report' => 'fit_report_path',
        ];

        foreach ($fileFields as $input => $column) {
            if ($request->hasFile($input)) {
                $data[$column] = $request->file($input)->store("clients/{$input}", 'public');
            }

            unset($data[$input]);
        }

        $previousPaid = (float) ($client->partial_paid_amount ?? 0);
        $newPaid = (float) ($data['partial_paid_amount'] ?? 0);
        $delta = $newPaid - $previousPaid;

        if ($delta < 0) {
            return back()
                ->withErrors(['partial_paid_amount' => 'Paid amount cannot be less than current paid amount (৳' . number_format($previousPaid) . '). Use the Refund option instead.'])
                ->withInput();
        }

        if ($delta > 0 && ! $paymentSource) {
            return back()
                ->withErrors(['payment_source' => 'Please select who made the payment.'])
                ->withInput();
        }

        if ($delta > 0 && $paymentSource === 'agent' && ! $paymentAgentId) {
            return back()
                ->withErrors(['payment_agent_id' => 'Please select an agent for agent payments.'])
                ->withInput();
        }

        $client->update($data);

        if ($delta > 0) {
            $client->payments()->create([
                'type' => 'payment',
                'source' => $paymentSource ?? 'client',
                'agent_id' => ($paymentSource === 'agent') ? $paymentAgentId : null,
                'amount' => $delta,
                'payment_date' => $data['partial_payment_date'] ?? now()->toDateString(),
                'created_by' => auth()->id(),
            ]);
        }

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client updated successfully.');
    }

    public function payVat(Request $request, Client $client)
    {
        $validated = $request->validate([
            'vat_chalan_number' => 'required|string|max:255',
            'vat_payment_date' => 'required|date',
        ]);

        // Calculate current unpaid VAT
        $vatReceivable = \App\Models\IncomeEntry::where('client_id', $client->id)
            ->sum('vat_amount');
        $vatUnpaid = max(0, $vatReceivable - $client->vat_paid_amount);

        // Get the active accounting period
        $period = \App\Models\AccountingPeriod::where('status', 'active')
            ->orWhere('status', 'draft')
            ->latest()
            ->first();

        if (!$period) {
            return redirect()->back()->withErrors(['error' => 'No active accounting period found.']);
        }

        // Create VAT payment record for the active period
        $period->vatPayments()->create([
            'payment_type' => 'individual',
            'client_id' => $client->id,
            'payment_amount' => $vatUnpaid,
            'chalan_number' => $validated['vat_chalan_number'],
            'payment_date' => $validated['vat_payment_date'],
            'notes' => 'Paid from client page',
        ]);

        // Update client VAT status
        $client->update([
            'vat_paid' => true,
            'vat_paid_amount' => $client->vat_paid_amount + $vatUnpaid,
            'vat_chalan_number' => $validated['vat_chalan_number'],
            'vat_payment_date' => $validated['vat_payment_date'],
        ]);

        return redirect()->back()->with('success', 'VAT payment recorded successfully.');
    }

    public function unpayVat(Client $client)
    {
        // Delete any VAT payment records for this client (both bulk and individual)
        \App\Models\VATPayment::where('client_id', $client->id)->delete();

        // Reset client VAT status
        $client->update([
            'vat_paid' => false,
            'vat_paid_amount' => 0,
            'vat_chalan_number' => null,
            'vat_payment_date' => null,
        ]);

        return redirect()->back()->with('success', 'VAT payment status reset successfully.');
    }

    public function refund(Request $request, Client $client)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'nullable|string|max:255',
            'payment_source' => 'required|in:client,agent',
            'agent_id' => 'nullable|required_if:payment_source,agent|exists:agents,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        // For old clients without payment records, fall back to calculated paid amount
        $hasPaymentRecords = $client->payments()->exists();
        $totalFee = (float) ($client->total_fee ?? 0);
        $due = (float) ($client->current_due ?? 0);
        $paid = max(0, $totalFee - $due);

        $totalReceived = $hasPaymentRecords
            ? (float) $client->payments()->where('type', 'payment')->sum('amount')
            : $paid;
        $totalRefunded = (float) $client->payments()->where('type', 'refund')->sum('amount');
        $maxRefundable = $totalReceived - $totalRefunded;

        if ($validated['amount'] > $maxRefundable) {
            return back()->withErrors([
                'amount' => 'Refund amount cannot exceed refundable balance (৳' . number_format($maxRefundable, 2) . ').',
            ])->withInput();
        }

        $client->payments()->create([
            'type' => 'refund',
            'source' => $validated['payment_source'],
            'agent_id' => $validated['payment_source'] === 'agent' ? $validated['agent_id'] : null,
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'payment_date' => $validated['payment_date'],
            'notes' => $validated['notes'],
            'created_by' => auth()->id(),
        ]);

        $client->update([
            'partial_paid_amount' => max(0, (float) $client->partial_paid_amount - $validated['amount']),
            'current_due' => (float) $client->current_due + $validated['amount'],
        ]);

        return redirect()->route('clients.edit', $client)
            ->with('success', 'Refund of ৳' . number_format($validated['amount'], 2) . ' processed successfully.');
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Client deleted successfully.');
    }

    private function formData(): array
    {
        $jobSectors = JobSector::whereNull('parent_id')
            ->with('children')
            ->orderBy('name')
            ->get()
            ->map(function ($sector) {
                return [
                    'id' => $sector->id,
                    'name' => $sector->name,
                    'children' => $sector->children->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                        ];
                    })->toArray(),
                ];
            });

        $visaStatusOptions = [
            ['value' => 'pending', 'label' => 'Pending'],
            ['value' => 'processing', 'label' => 'Processing'],
            ['value' => 'completed', 'label' => 'Completed'],
            ['value' => 'rejected', 'label' => 'Rejected'],
        ];

        $visaStageOptions = [
            ['value' => 'document_collection', 'label' => 'Document Collection'],
            ['value' => 'medical_processing', 'label' => 'Medical Processing'],
            ['value' => 'bd_company_processing', 'label' => 'BD Company Processing'],
            ['value' => 'online_submission', 'label' => 'Online Submission'],
            ['value' => 'calling_stage', 'label' => 'Calling Stage'],
            ['value' => 'evisa_stage', 'label' => 'E-Visa Processing'],
            ['value' => 'approved', 'label' => 'Approved'],
            ['value' => 'rejected', 'label' => 'Rejected'],
        ];

        $fitStatusOptions = [
            ['value' => 'pending', 'label' => 'Pending'],
            ['value' => 'fit', 'label' => 'Fit'],
            ['value' => 'unfit', 'label' => 'Unfit'],
        ];

        return [
            'jobSectors' => $jobSectors,
            'visaStatusOptions' => $visaStatusOptions,
            'visaStageOptions' => $visaStageOptions,
            'fitStatusOptions' => $fitStatusOptions,
            'agents' => \App\Models\Agent::select('id', 'name', 'mobile', 'district', 'address')->orderBy('name')->get(),
            'bdCompanies' => \App\Models\BdCompany::select('id', 'name', 'contact_person_phone', 'owner_phone', 'office_address')->orderBy('name')->get(),
            'foreignCompanies' => \App\Models\ForeignCompany::select('id', 'name', 'country', 'contact_person_phone', 'owner_phone', 'contact_person_name')->orderBy('name')->get(),
            'countries' => \App\Models\ForeignCompany::select('country')->distinct()->whereNotNull('country')->orderBy('country')->pluck('country'),
        ];
    }

    private function transformClient(Client $client): array
    {
        return [
            'id' => $client->id,
            'name' => $client->name,
            'organization_name' => $client->organization_name,
            'email' => $client->email,
            'nid_number' => $client->nid_number,
            'passport_number' => $client->passport_number,
            'date_of_birth' => optional($client->date_of_birth)?->toDateString(),
            'mobile' => $client->mobile,
            'district' => $client->district,
            'address' => $client->address,
            'job_sector' => $client->job_sector,
            'job_sub_sector' => $client->job_sub_sector,
            'foreign_company_country' => $client->foreign_company_country,
            'foreign_company_name' => $client->foreign_company_name,
            'foreign_company_email' => $client->foreign_company_email,
            'foreign_company_phone' => $client->foreign_company_phone,
            'agent_mobile' => $client->agent_mobile,
            'agent_district' => $client->agent_district,
            'agent_address' => $client->agent_address,
            'medical_fee' => $client->medical_fee,
            'fit_status' => $client->fit_status,
            'documents_submitted_to' => $client->documents_submitted_to,
            'documents_submission_phone' => $client->documents_submission_phone,
            'date_of_submission' => optional($client->date_of_submission)?->toDateString(),
            'expected_date_to_collect' => optional($client->expected_date_to_collect)?->toDateString(),
            'documents_collected_date' => optional($client->documents_collected_date)?->toDateString(),
            'total_fee' => $client->total_fee,
            'current_due' => $client->current_due,
            'partial_paid_amount' => $client->partial_paid_amount,
            'partial_payment_date' => optional($client->partial_payment_date)?->toDateString(),
            'next_payment_amount' => $client->next_payment_amount,
            'next_payment_date' => optional($client->next_payment_date)?->toDateString(),
            'final_payment' => $client->final_payment,
            'final_payment_date' => optional($client->final_payment_date)?->toDateString(),
            'notes' => $client->notes,
            'online_status' => $client->online_status,
            'calling_status' => $client->calling_status,
            'evisa_status' => $client->evisa_status,
            'agent_id' => $client->agent_id,
            'bd_company_id' => $client->bd_company_id,
            'foreign_company_id' => $client->foreign_company_id,
        ];
    }

    private function resolveStatus($holder): array
    {
        if (!$holder) {
            return ['value' => 'pending', 'label' => 'Pending at MITT', 'badge' => 'bg-amber-100 text-amber-700 ring-amber-200'];
        }

        if ($holder->processing_status === 'rejected') {
            return ['value' => 'rejected', 'label' => 'Rejected', 'badge' => 'bg-red-100 text-red-700 ring-red-200'];
        }

        if ($holder->processing_status === 'completed') {
            return ['value' => 'completed', 'label' => 'Completed', 'badge' => 'bg-green-100 text-green-700 ring-green-200'];
        }

        if (in_array($holder->holder_type, ['bd_company', 'foreign_company'])) {
            return ['value' => 'company_processing', 'label' => 'Company Processing', 'badge' => 'bg-blue-100 text-blue-700 ring-blue-200'];
        }

        return ['value' => 'pending', 'label' => 'Pending at MITT', 'badge' => 'bg-amber-100 text-amber-700 ring-amber-200'];
    }
}
