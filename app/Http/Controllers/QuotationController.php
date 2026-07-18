<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Client;
use App\Models\OfficeStaff;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class QuotationController extends Controller
{
    private const DEFAULT_TERMS = "1. This quotation is valid until the stated validity date unless otherwise agreed in writing.\n"
        . "2. Prices are based on information provided and may change due to airline, embassy, or third-party fee changes.\n"
        . "3. Government fees, taxes, and third-party charges are non-refundable where applicable.\n"
        . "4. Service delivery timelines depend on external authorities and are not guaranteed.\n"
        . "5. Visa approval/acceptance is subject to the relevant authority; no guarantees are provided.\n"
        . "6. Any additional document requirements communicated later must be provided promptly by the client.\n"
        . "7. Cancellation after confirmation may incur charges based on vendor policy.\n"
        . "8. Partial payment may be required before processing begins.\n"
        . "9. This quotation is confidential and intended only for the named client.\n"
        . "10. By proceeding, the client agrees to these terms and conditions.";

    public function index(Request $request)
    {
        $search = $request->input('search');

        $quotations = Quotation::with(['client:id,name', 'quotationMaker:id,name', 'creator:id,name'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('client_name', 'like', "%{$search}%")
                      ->orWhere('quotation_no', 'like', "%{$search}%")
                      ->orWhere('client_mobile', 'like', "%{$search}%");
                });
            })
            ->latest('quotation_date')
            ->latest('id')
            ->get()
            ->map(fn ($q) => [
                'id' => $q->id,
                'quotation_no' => $q->quotation_no,
                'quotation_date' => $q->quotation_date?->format('Y-m-d'),
                'client_name' => $q->client_name,
                'service_category' => $q->service_category,
                'service_type' => $q->service_type,
                'quotation_maker' => $q->quotationMaker?->name,
                'created_by' => $q->creator?->name,
                'pdf_path' => $q->pdf_path,
            ]);

        return Inertia::render('Quotations/Index', [
            'quotations' => $quotations,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function export(Request $request, ?string $type = null)
    {
        $search = $request->input('search');

        $quotations = Quotation::with(['client:id,name', 'quotationMaker:id,name', 'creator:id,name'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('client_name', 'like', "%{$search}%")
                        ->orWhere('quotation_no', 'like', "%{$search}%")
                        ->orWhere('client_mobile', 'like', "%{$search}%");
                });
            })
            ->latest('quotation_date')
            ->latest('id')
            ->get();

        if ($type === 'pdf') {
            $fileName = 'quotations-report-' . now()->format('Y-m-d') . '.pdf';

            return Pdf::loadView('pdfs.quotation_list_report', [
                'quotations' => $quotations,
            ])->download($fileName);
        }

        $handle = fopen('php://memory', 'w');

        fputcsv($handle, [
            'Quotation No',
            'Date',
            'Client',
            'Service Category',
            'Service Type',
            'Maker',
            'Created By',
        ]);

        foreach ($quotations as $quotation) {
            fputcsv($handle, [
                $quotation->quotation_no,
                $quotation->quotation_date?->format('Y-m-d'),
                $quotation->client_name,
                $quotation->service_category,
                $quotation->service_type,
                $quotation->quotationMaker?->name,
                $quotation->creator?->name,
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $fileName = 'quotations-report-' . now()->format('Y-m-d') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function create()
    {
        $clients = Client::with('agent:id,name')
            ->select('id', 'name', 'organization_name', 'email', 'mobile', 'passport_number', 'agent_id')
            ->orderBy('name')
            ->get()
            ->map(fn($client) => [
                'id' => $client->id,
                'name' => $client->name,
                'organization_name' => $client->organization_name,
                'email' => $client->email,
                'mobile' => $client->mobile,
                'passport_number' => $client->passport_number,
                'agent_name' => $client->agent?->name,
            ]);

        $officeStaff = OfficeStaff::select('id', 'name')
            ->orderBy('name')
            ->get();

        $subcategories = Subcategory::where('type', 'income')
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'vat_rate' => (float) $item->vat_rate,
                'category' => $item->category,
            ])
            ->values()
            ->toArray();

        $agents = Agent::select('id', 'name')
            ->orderBy('name')
            ->get();

        $organizations = Client::whereNotNull('organization_name')
            ->where('organization_name', '!=', '')
            ->distinct()
            ->orderBy('organization_name')
            ->pluck('organization_name')
            ->values();

        return Inertia::render('Quotations/Create', [
            'clients' => $clients,
            'officeStaff' => $officeStaff,
            'subcategories' => $subcategories,
            'agents' => $agents,
            'organizations' => $organizations,
            'defaultTerms' => self::DEFAULT_TERMS,
            'companyDefaults' => [
                'phone' => '+88 01716 864 109 / +88 01332 502 234',
                'email' => 'zulia.tourstravelsbd@gmail.com',
                'address' => '25,26,27, Kazi Nazrul Islam Avenue, Banglamotor Trade Center, (Former Happy Rahman Plaza) 4th Floor, Banglamotor Shahbagh, Dhaka-1000.',
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'organization_name' => ['nullable', 'string', 'max:255'],
            'client_email' => ['nullable', 'email', 'max:255'],
            'service_category' => ['required', 'in:travel_tourism,manpower_exporting,student_package,other_income'],
            'service_type' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'quotation_maker_id' => ['required', 'exists:office_staff,id'],
            'terms_type' => ['required', 'in:default,custom'],
            'terms_text' => ['nullable', 'string'],
            'valid_until' => ['required', 'date'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_address' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.service_description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_type' => ['required', 'in:percent,amount'],
            'items.*.discount_value' => ['nullable', 'numeric', 'min:0'],
            'items.*.vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $plainText = trim(strip_tags($validated['description']));
        $wordCount = $plainText === '' ? 0 : count(preg_split('/\s+/u', $plainText));
        if ($wordCount > 350) {
            return back()
                ->withErrors(['description' => 'Description must be within 350 words.'])
                ->withInput();
        }

        if ($validated['terms_type'] === 'custom' && empty(trim($validated['terms_text'] ?? ''))) {
            return back()
                ->withErrors(['terms_text' => 'Please provide custom terms and conditions.'])
                ->withInput();
        }

        $client = Client::findOrFail($validated['client_id']);

        $items = $this->normalizeQuotationItems($validated['items']);

        $quotation = DB::transaction(function () use ($validated, $client, $items) {
            $year = now()->year;
            $nextSequence = Quotation::where('quotation_year', $year)
                ->lockForUpdate()
                ->max('sequence');
            $nextSequence = $nextSequence ? $nextSequence + 1 : 1;

            $quotationNo = sprintf('QTN-%d-%04d', $year, $nextSequence);

            $quotation = Quotation::create([
                'quotation_no' => $quotationNo,
                'quotation_year' => $year,
                'sequence' => $nextSequence,
                'quotation_date' => now()->toDateString(),
                'client_id' => $client->id,
                'client_name' => $client->name,
                'organization_name' => $validated['organization_name'] ?: $client->organization_name,
                'client_mobile' => $client->mobile,
                'client_email' => $validated['client_email'] ?: $client->email,
                'service_category' => $validated['service_category'],
                'service_type' => $validated['service_type'],
                'description' => $validated['description'],
                'company_phone' => $validated['company_phone'] ?? null,
                'company_email' => $validated['company_email'] ?? null,
                'company_address' => $validated['company_address'] ?? null,
                'quotation_maker_id' => $validated['quotation_maker_id'],
                'created_by' => auth()->id(),
                'terms_type' => $validated['terms_type'],
                'terms_text' => $validated['terms_type'] === 'custom'
                    ? $validated['terms_text']
                    : self::DEFAULT_TERMS,
                'valid_until' => $validated['valid_until'],
            ]);

            foreach ($items as $index => $item) {
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'sl' => $index + 1,
                    'service_description' => $item['service_description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'price' => $item['price'],
                    'discount_type' => $item['discount_type'],
                    'discount_value' => $item['discount_value'],
                    'discount_amount' => $item['discount_amount'],
                    'vat_rate' => $item['vat_rate'],
                    'vat_amount' => $item['vat_amount'],
                    'line_total' => $item['line_total'],
                ]);
            }

            return $quotation;
        });

        $quotation->load(['client', 'quotationMaker', 'items', 'creator']);

        $pdfPath = $this->storePdf($quotation);

        $quotation->update([
            'pdf_path' => $pdfPath,
        ]);

        return redirect()
            ->route('quotations.index')
            ->with('success', 'Quotation created successfully.');
    }

    public function edit(Quotation $quotation)
    {
        $quotation->load(['client', 'quotationMaker', 'items']);

        $clients = Client::with('agent:id,name')
            ->select('id', 'name', 'organization_name', 'email', 'mobile', 'passport_number', 'agent_id')
            ->orderBy('name')
            ->get()
            ->map(fn($client) => [
                'id' => $client->id,
                'name' => $client->name,
                'organization_name' => $client->organization_name,
                'email' => $client->email,
                'mobile' => $client->mobile,
                'passport_number' => $client->passport_number,
                'agent_name' => $client->agent?->name,
            ]);

        $officeStaff = OfficeStaff::select('id', 'name')
            ->orderBy('name')
            ->get();

        $subcategories = Subcategory::where('type', 'income')
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'vat_rate' => (float) $item->vat_rate,
                'category' => $item->category,
            ])
            ->values()
            ->toArray();

        return Inertia::render('Quotations/Edit', [
            'quotation' => [
                'id' => $quotation->id,
                'quotation_no' => $quotation->quotation_no,
                'quotation_date' => $quotation->quotation_date?->format('Y-m-d'),
                'client_id' => $quotation->client_id,
                'organization_name' => $quotation->organization_name,
                'client_mobile' => $quotation->client_mobile,
                'client_email' => $quotation->client_email,
                'service_category' => $quotation->service_category,
                'service_type' => $quotation->service_type,
                'description' => $quotation->description,
                'company_phone' => $quotation->company_phone,
                'company_email' => $quotation->company_email,
                'company_address' => $quotation->company_address,
                'quotation_maker_id' => $quotation->quotation_maker_id,
                'terms_type' => $quotation->terms_type,
                'terms_text' => $quotation->terms_text,
                'valid_until' => $quotation->valid_until?->format('Y-m-d'),
                'items' => $quotation->items->map(fn ($item) => [
                    'service_description' => $item->service_description,
                    'quantity' => (float) ($item->quantity ?? 1),
                    'unit_price' => (float) ($item->unit_price ?? $item->price),
                    'price' => (float) $item->price,
                    'discount_type' => $item->discount_type ?? 'percent',
                    'discount_value' => (float) ($item->discount_value ?? 0),
                    'vat_rate' => (float) ($item->vat_rate ?? 0),
                ])->toArray(),
            ],
            'clients' => $clients,
            'officeStaff' => $officeStaff,
            'subcategories' => $subcategories,
            'defaultTerms' => self::DEFAULT_TERMS,
            'companyDefaults' => [
                'phone' => '+88 01716 864 109 / +88 01332 502 234',
                'email' => 'zulia.tourstravelsbd@gmail.com',
                'address' => '25,26,27, Kazi Nazrul Islam Avenue, Banglamotor Trade Center, (Former Happy Rahman Plaza) 4th Floor, Banglamotor Shahbagh, Dhaka-1000.',
            ],
        ]);
    }

    public function update(Request $request, Quotation $quotation)
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'organization_name' => ['nullable', 'string', 'max:255'],
            'client_email' => ['nullable', 'email', 'max:255'],
            'service_category' => ['required', 'in:travel_tourism,manpower_exporting,student_package,other_income'],
            'service_type' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'quotation_maker_id' => ['required', 'exists:office_staff,id'],
            'terms_type' => ['required', 'in:default,custom'],
            'terms_text' => ['nullable', 'string'],
            'valid_until' => ['required', 'date'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_address' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.service_description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_type' => ['required', 'in:percent,amount'],
            'items.*.discount_value' => ['nullable', 'numeric', 'min:0'],
            'items.*.vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $plainText = trim(strip_tags($validated['description']));
        $wordCount = $plainText === '' ? 0 : count(preg_split('/\s+/u', $plainText));
        if ($wordCount > 350) {
            return back()
                ->withErrors(['description' => 'Description must be within 350 words.'])
                ->withInput();
        }

        if ($validated['terms_type'] === 'custom' && empty(trim($validated['terms_text'] ?? ''))) {
            return back()
                ->withErrors(['terms_text' => 'Please provide custom terms and conditions.'])
                ->withInput();
        }

        $client = Client::findOrFail($validated['client_id']);

        $items = $this->normalizeQuotationItems($validated['items']);

        DB::transaction(function () use ($quotation, $validated, $client, $items) {
            $quotation->update([
                'client_id' => $client->id,
                'client_name' => $client->name,
                'organization_name' => $validated['organization_name'] ?: $client->organization_name,
                'client_mobile' => $client->mobile,
                'client_email' => $validated['client_email'] ?: $client->email,
                'service_category' => $validated['service_category'],
                'service_type' => $validated['service_type'],
                'description' => $validated['description'],
                'company_phone' => $validated['company_phone'] ?? null,
                'company_email' => $validated['company_email'] ?? null,
                'company_address' => $validated['company_address'] ?? null,
                'quotation_maker_id' => $validated['quotation_maker_id'],
                'terms_type' => $validated['terms_type'],
                'terms_text' => $validated['terms_type'] === 'custom'
                    ? $validated['terms_text']
                    : self::DEFAULT_TERMS,
                'valid_until' => $validated['valid_until'],
            ]);

            // Delete existing items and create new ones
            $quotation->items()->delete();
            foreach ($items as $index => $item) {
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'sl' => $index + 1,
                    'service_description' => $item['service_description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'price' => $item['price'],
                    'discount_type' => $item['discount_type'],
                    'discount_value' => $item['discount_value'],
                    'discount_amount' => $item['discount_amount'],
                    'vat_rate' => $item['vat_rate'],
                    'vat_amount' => $item['vat_amount'],
                    'line_total' => $item['line_total'],
                ]);
            }
        });

        $quotation->load(['client', 'quotationMaker', 'items', 'creator']);

        // Regenerate PDF
        $pdfPath = $this->storePdf($quotation);
        $quotation->update(['pdf_path' => $pdfPath]);

        return redirect()
            ->route('quotations.index')
            ->with('success', 'Quotation updated successfully.');
    }


    public function show(Quotation $quotation)
    {
        $quotation->load(['client', 'quotationMaker', 'items', 'creator']);
        $summary = $this->computeQuotationSummary($quotation);

        return Inertia::render('Quotations/Show', [
            'quotation' => [
                'id' => $quotation->id,
                'quotation_no' => $quotation->quotation_no,
                'quotation_date' => $quotation->quotation_date?->format('Y-m-d'),
                'valid_until' => $quotation->valid_until?->format('Y-m-d'),
                'client_name' => $quotation->client_name,
                'organization_name' => $quotation->organization_name,
                'client_mobile' => $quotation->client_mobile,
                'client_email' => $quotation->client_email,
                'service_category' => $quotation->service_category,
                'service_type' => $quotation->service_type,
                'description' => $quotation->description,
                'company_phone' => $quotation->company_phone,
                'company_email' => $quotation->company_email,
                'company_address' => $quotation->company_address,
                'subtotal' => $summary['subtotal'],
                'discount_amount' => $summary['discount_amount'],
                'vat_amount' => $summary['vat_amount'],
                'total_amount' => $summary['total_amount'],
                'quotation_maker' => $quotation->quotationMaker?->name,
                'created_by' => $quotation->creator?->name,
                'terms_text' => $quotation->terms_text,
                'pdf_path' => $quotation->pdf_path,
                'items' => $quotation->items->map(fn ($item) => [
                    'id' => $item->id,
                    'sl' => $item->sl,
                    'service_description' => $item->service_description,
                    'quantity' => (float) ($item->quantity ?? 1),
                    'unit_price' => (float) ($item->unit_price ?? $item->price),
                    'price' => (float) $item->price,
                    'discount_type' => $item->discount_type ?? 'percent',
                    'discount_value' => (float) ($item->discount_value ?? 0),
                    'discount_amount' => (float) ($item->discount_amount ?? 0),
                    'vat_rate' => (float) ($item->vat_rate ?? 0),
                    'vat_amount' => (float) ($item->vat_amount ?? 0),
                    'line_total' => (float) ($item->line_total ?? 0),
                ]),
            ],
        ]);
    }

    public function download(Quotation $quotation)
    {
        if (! $quotation->pdf_path || ! Storage::disk('public')->exists($quotation->pdf_path)) {
            $quotation->load(['client', 'quotationMaker', 'items', 'creator']);
            $pdfPath = $this->storePdf($quotation);
            $quotation->update(['pdf_path' => $pdfPath]);
        }

        return Storage::disk('public')->download(
            $quotation->pdf_path,
            $quotation->quotation_no . '.pdf'
        );
    }

    private function storePdf(Quotation $quotation): string
    {
        $path = 'quotations/' . $quotation->quotation_date->format('Y/m') . '/' . $quotation->quotation_no . '.pdf';

        $summary = $this->computeQuotationSummary($quotation);

        $pdf = Pdf::loadView('pdfs.quotation_report', [
            'quotation' => $quotation,
            'summary' => $summary,
        ])->setPaper('A4');

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    private function normalizeQuotationItems(array $items): array
    {
        return collect($items)->values()->map(function ($item) {
            $quantity = (float) $item['quantity'];
            $unitPrice = (float) $item['unit_price'];
            $baseAmount = $quantity * $unitPrice;
            $discountValue = (float) ($item['discount_value'] ?? 0);
            $discountAmount = $item['discount_type'] === 'percent'
                ? ($baseAmount * $discountValue) / 100
                : $discountValue;
            $discountAmount = min($baseAmount, max(0, $discountAmount));
            $taxableAmount = max(0, $baseAmount - $discountAmount);
            $vatRate = (float) ($item['vat_rate'] ?? 0);
            $vatAmount = ($taxableAmount * $vatRate) / 100;
            $lineTotal = $taxableAmount + $vatAmount;

            return [
                'service_description' => $item['service_description'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'price' => $baseAmount,
                'discount_type' => $item['discount_type'],
                'discount_value' => $discountValue,
                'discount_amount' => $discountAmount,
                'vat_rate' => $vatRate,
                'vat_amount' => $vatAmount,
                'line_total' => $lineTotal,
            ];
        })->toArray();
    }

    private function computeQuotationSummary(Quotation $quotation): array
    {
        $subtotal = 0;
        $discountAmount = 0;
        $vatAmount = 0;
        $totalAmount = 0;

        foreach ($quotation->items as $item) {
            $quantity = (float) ($item->quantity ?? 1);
            $unitPrice = (float) ($item->unit_price ?? $item->price);
            $baseAmount = $quantity * $unitPrice;
            $subtotal += $baseAmount;
            $discountAmount += (float) ($item->discount_amount ?? 0);
            $vatAmount += (float) ($item->vat_amount ?? 0);
            $totalAmount += (float) ($item->line_total ?? $baseAmount);
        }

        return [
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'vat_amount' => $vatAmount,
            'total_amount' => $totalAmount,
        ];
    }
}
