<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Models\JobSector;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::query()
            ->with(['bdCompany', 'foreignCompany', 'agent', 'documentLocation'])
            ->latest()
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
                    'foreign_company' => $client->foreignCompany?->name,
                    'agent_name' => $client->agent_name ?? $client->agent?->name,
                    'agent' => $client->agent?->name,
                    'status' => $status['label'],
                    'status_value' => $status['value'],
                    'status_badge' => $status['badge'],
                    'current_due' => $client->current_due,
                    'vat_receivable' => (float) $vatReceivable,
                    'vat_paid' => false, // You can add logic to track if VAT is paid
                    'photo_url' => $client->photo_path ? asset('storage/'.$client->photo_path) : null,
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

    public function edit(Client $client)
    {
        return Inertia::render('Clients/Create', [
            ...$this->formData(),
            'client' => $this->transformClient($client),
            'mode' => 'edit',
        ]);
    }

    public function show(Client $client)
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
            'description' => 'Documents received and being processed by agency staff',
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
        ],
            'stages' => $stages,
            'currentStageIndex' => $stageIndex === false ? null : $stageIndex,
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

        $data = collect($data)->except(array_keys($fileFields))->toArray();

        Client::create($data);

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client created successfully.');
    }

    public function update(UpdateClientRequest $request, Client $client)
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

            unset($data[$input]);
        }

        $client->update($data);

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client updated successfully.');
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
        if (! $holder) {
            return ['value' => 'pending', 'label' => 'Pending at Agency', 'badge' => 'bg-amber-100 text-amber-700 ring-amber-200'];
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

        return ['value' => 'pending', 'label' => 'Pending at Agency', 'badge' => 'bg-amber-100 text-amber-700 ring-amber-200'];
    }
}
