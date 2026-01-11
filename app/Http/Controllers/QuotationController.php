<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\OfficeStaff;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Spatie\LaravelPdf\Facades\Pdf;

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

    public function index()
    {
        $quotations = Quotation::with(['client:id,name', 'quotationMaker:id,name', 'creator:id,name'])
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
        ]);
    }

    public function create()
    {
        $clients = Client::select('id', 'name', 'organization_name', 'email', 'mobile')
            ->orderBy('name')
            ->get();

        $officeStaff = OfficeStaff::select('id', 'name')
            ->orderBy('name')
            ->get();

        $subcategories = Subcategory::where('type', 'income')
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category')
            ->map(fn ($items) => $items->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
            ])->values())
            ->toArray();

        return Inertia::render('Quotations/Create', [
            'clients' => $clients,
            'officeStaff' => $officeStaff,
            'subcategories' => $subcategories,
            'defaultTerms' => self::DEFAULT_TERMS,
            'companyDefaults' => [
                'phone' => '+8801743-879171',
                'email' => 'info@mefwayinternationaltravelandtours.com',
                'address' => 'Confidence Center, Level - 1, Shop - 114, Shahjadpur, Gulshan - 2, Dhaka, Bangladesh.',
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
            'items.*.price' => ['required', 'numeric', 'min:0'],
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

        $quotation = DB::transaction(function () use ($validated, $client) {
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

            $items = collect($validated['items'])->values();
            foreach ($items as $index => $item) {
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'sl' => $index + 1,
                    'service_description' => $item['service_description'],
                    'price' => $item['price'],
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

    public function show(Quotation $quotation)
    {
        $quotation->load(['client', 'quotationMaker', 'items', 'creator']);

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
                'quotation_maker' => $quotation->quotationMaker?->name,
                'created_by' => $quotation->creator?->name,
                'terms_text' => $quotation->terms_text,
                'pdf_path' => $quotation->pdf_path,
                'items' => $quotation->items->map(fn ($item) => [
                    'id' => $item->id,
                    'sl' => $item->sl,
                    'service_description' => $item->service_description,
                    'price' => (float) $item->price,
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

        Pdf::view('pdfs.quotation_report', [
            'quotation' => $quotation,
        ])
            ->format('A4')
            ->disk('public', 'public')
            ->save($path);

        return $path;
    }
}
