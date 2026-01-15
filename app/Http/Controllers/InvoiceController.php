<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
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

        $invoices = Invoice::with(['client:id,name', 'creator:id,name'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('client_name', 'like', "%{$search}%")
                      ->orWhere('invoice_no', 'like', "%{$search}%")
                      ->orWhere('client_mobile', 'like', "%{$search}%");
                });
            })
            ->latest('invoice_date')
            ->latest('id')
            ->get()
            ->map(fn ($invoice) => [
                'id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'invoice_date' => $invoice->invoice_date?->format('Y-m-d'),
                'client_name' => $invoice->client_name,
                'service_category' => $invoice->service_category,
                'service_type' => $invoice->service_type,
                'total_amount' => (float) $invoice->total_amount,
                'paid_amount' => (float) $invoice->paid_amount,
                'due_amount' => (float) $invoice->due_amount,
                'payment_status' => $invoice->payment_status,
                'created_by' => $invoice->creator?->name,
                'pdf_path' => $invoice->pdf_path,
            ]);

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create()
    {
        $clients = Client::select('id', 'name', 'organization_name', 'email', 'mobile')
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

        return Inertia::render('Invoices/Create', [
            'clients' => $clients,
            'subcategories' => $subcategories,
            'defaultTerms' => self::DEFAULT_TERMS,
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
            'description' => ['nullable', 'string'],
            'terms_type' => ['required', 'in:default,custom'],
            'terms_text' => ['nullable', 'string'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_address' => ['nullable', 'string'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_date' => ['nullable', 'date'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.service_description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_type' => ['required', 'in:percent,amount'],
            'items.*.discount_value' => ['nullable', 'numeric', 'min:0'],
            'items.*.vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        if ($validated['terms_type'] === 'custom' && empty(trim($validated['terms_text'] ?? ''))) {
            return back()
                ->withErrors(['terms_text' => 'Please provide custom terms and conditions.'])
                ->withInput();
        }

        $client = Client::findOrFail($validated['client_id']);
        $items = collect($validated['items'])->values();
        $subtotal = 0;
        $discountAmount = 0;
        $vatAmount = 0;
        $totalAmount = 0;

        $items = $items->map(function ($item) use (&$subtotal, &$discountAmount, &$vatAmount, &$totalAmount) {
            $quantity = (float) $item['quantity'];
            $unitPrice = (float) $item['unit_price'];
            $baseAmount = $quantity * $unitPrice;
            $discountValue = (float) ($item['discount_value'] ?? 0);
            $itemDiscount = $item['discount_type'] === 'percent'
                ? ($baseAmount * $discountValue) / 100
                : $discountValue;
            $itemDiscount = min($baseAmount, max(0, $itemDiscount));
            $taxableAmount = max(0, $baseAmount - $itemDiscount);
            $itemVatRate = (float) ($item['vat_rate'] ?? 0);
            $itemVatAmount = ($taxableAmount * $itemVatRate) / 100;
            $lineTotal = $taxableAmount + $itemVatAmount;

            $subtotal += $baseAmount;
            $discountAmount += $itemDiscount;
            $vatAmount += $itemVatAmount;
            $totalAmount += $lineTotal;

            return [
                'service_description' => $item['service_description'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_type' => $item['discount_type'],
                'discount_value' => $discountValue,
                'discount_amount' => $itemDiscount,
                'vat_rate' => $itemVatRate,
                'vat_amount' => $itemVatAmount,
                'line_total' => $lineTotal,
            ];
        });
        $paidAmount = (float) ($validated['paid_amount'] ?? 0);

        if ($paidAmount > $totalAmount) {
            return back()
                ->withErrors(['paid_amount' => 'Paid amount cannot exceed total amount.'])
                ->withInput();
        }

        $dueAmount = max(0, $totalAmount - $paidAmount);
        $paymentStatus = $paidAmount <= 0 ? 'unpaid' : ($dueAmount <= 0 ? 'paid' : 'partial');

        $invoice = DB::transaction(function () use ($validated, $client, $items, $subtotal, $discountAmount, $vatAmount, $totalAmount, $paidAmount, $dueAmount, $paymentStatus) {
            $year = now()->year;
            $nextSequence = Invoice::where('invoice_year', $year)
                ->lockForUpdate()
                ->max('sequence');
            $nextSequence = $nextSequence ? $nextSequence + 1 : 1;

            $invoiceNo = sprintf('INV-%d-%04d', $year, $nextSequence);

            $invoice = Invoice::create([
                'invoice_no' => $invoiceNo,
                'invoice_year' => $year,
                'sequence' => $nextSequence,
                'invoice_date' => now()->toDateString(),
                'client_id' => $client->id,
                'client_name' => $client->name,
                'organization_name' => $validated['organization_name'] ?: $client->organization_name,
                'client_mobile' => $client->mobile,
                'client_email' => $validated['client_email'] ?: $client->email,
                'service_category' => $validated['service_category'],
                'service_type' => $validated['service_type'],
                'description' => $validated['description'] ?? null,
                'terms_type' => $validated['terms_type'],
                'terms_text' => $validated['terms_type'] === 'custom'
                    ? $validated['terms_text']
                    : self::DEFAULT_TERMS,
                'company_phone' => $validated['company_phone'] ?? null,
                'company_email' => $validated['company_email'] ?? null,
                'company_address' => $validated['company_address'] ?? null,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'vat_rate' => 0,
                'vat_amount' => $vatAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'payment_status' => $paymentStatus,
                'payment_date' => $validated['payment_date'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($items as $index => $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'sl' => $index + 1,
                    'service_description' => $item['service_description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_type' => $item['discount_type'],
                    'discount_value' => $item['discount_value'],
                    'discount_amount' => $item['discount_amount'],
                    'vat_rate' => $item['vat_rate'],
                    'vat_amount' => $item['vat_amount'],
                    'line_total' => $item['line_total'],
                ]);
            }

            return $invoice;
        });

        $invoice->load(['client', 'items', 'creator']);

        $pdfPath = $this->storePdf($invoice);

        $invoice->update([
            'pdf_path' => $pdfPath,
        ]);

        return redirect()
            ->route('invoices.index')
            ->with('success', 'Invoice created successfully.');
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load(['client', 'items']);

        $clients = Client::select('id', 'name', 'organization_name', 'email', 'mobile')
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

        return Inertia::render('Invoices/Edit', [
            'invoice' => [
                'id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'invoice_date' => $invoice->invoice_date?->format('Y-m-d'),
                'client_id' => $invoice->client_id,
                'organization_name' => $invoice->organization_name,
                'client_mobile' => $invoice->client_mobile,
                'client_email' => $invoice->client_email,
                'service_category' => $invoice->service_category,
                'service_type' => $invoice->service_type,
                'description' => $invoice->description,
                'terms_type' => $invoice->terms_type ?? 'default',
                'terms_text' => $invoice->terms_text ?? self::DEFAULT_TERMS,
                'company_phone' => $invoice->company_phone,
                'company_email' => $invoice->company_email,
                'company_address' => $invoice->company_address,
                'discount_amount' => (float) $invoice->discount_amount,
                'vat_rate' => (float) $invoice->vat_rate,
                'paid_amount' => (float) $invoice->paid_amount,
                'payment_date' => $invoice->payment_date?->format('Y-m-d'),
                'payment_method' => $invoice->payment_method,
                'items' => $invoice->items->map(fn ($item) => [
                    'service_description' => $item->service_description,
                    'quantity' => (float) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'discount_type' => $item->discount_type ?? 'percent',
                    'discount_value' => (float) ($item->discount_value ?? 0),
                    'vat_rate' => (float) ($item->vat_rate ?? 0),
                ])->toArray(),
            ],
            'clients' => $clients,
            'subcategories' => $subcategories,
            'defaultTerms' => self::DEFAULT_TERMS,
        ]);
    }

    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'organization_name' => ['nullable', 'string', 'max:255'],
            'client_email' => ['nullable', 'email', 'max:255'],
            'service_category' => ['required', 'in:travel_tourism,manpower_exporting,student_package,other_income'],
            'service_type' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'terms_type' => ['required', 'in:default,custom'],
            'terms_text' => ['nullable', 'string'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_address' => ['nullable', 'string'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_date' => ['nullable', 'date'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.service_description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_type' => ['required', 'in:percent,amount'],
            'items.*.discount_value' => ['nullable', 'numeric', 'min:0'],
            'items.*.vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        if ($validated['terms_type'] === 'custom' && empty(trim($validated['terms_text'] ?? ''))) {
            return back()
                ->withErrors(['terms_text' => 'Please provide custom terms and conditions.'])
                ->withInput();
        }

        $client = Client::findOrFail($validated['client_id']);
        $items = collect($validated['items'])->values();
        $subtotal = 0;
        $discountAmount = 0;
        $vatAmount = 0;
        $totalAmount = 0;

        $items = $items->map(function ($item) use (&$subtotal, &$discountAmount, &$vatAmount, &$totalAmount) {
            $quantity = (float) $item['quantity'];
            $unitPrice = (float) $item['unit_price'];
            $baseAmount = $quantity * $unitPrice;
            $discountValue = (float) ($item['discount_value'] ?? 0);
            $itemDiscount = $item['discount_type'] === 'percent'
                ? ($baseAmount * $discountValue) / 100
                : $discountValue;
            $itemDiscount = min($baseAmount, max(0, $itemDiscount));
            $taxableAmount = max(0, $baseAmount - $itemDiscount);
            $itemVatRate = (float) ($item['vat_rate'] ?? 0);
            $itemVatAmount = ($taxableAmount * $itemVatRate) / 100;
            $lineTotal = $taxableAmount + $itemVatAmount;

            $subtotal += $baseAmount;
            $discountAmount += $itemDiscount;
            $vatAmount += $itemVatAmount;
            $totalAmount += $lineTotal;

            return [
                'service_description' => $item['service_description'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_type' => $item['discount_type'],
                'discount_value' => $discountValue,
                'discount_amount' => $itemDiscount,
                'vat_rate' => $itemVatRate,
                'vat_amount' => $itemVatAmount,
                'line_total' => $lineTotal,
            ];
        });
        $paidAmount = (float) ($validated['paid_amount'] ?? 0);

        if ($paidAmount > $totalAmount) {
            return back()
                ->withErrors(['paid_amount' => 'Paid amount cannot exceed total amount.'])
                ->withInput();
        }

        $dueAmount = max(0, $totalAmount - $paidAmount);
        $paymentStatus = $paidAmount <= 0 ? 'unpaid' : ($dueAmount <= 0 ? 'paid' : 'partial');

        DB::transaction(function () use ($invoice, $validated, $client, $items, $subtotal, $discountAmount, $vatAmount, $totalAmount, $paidAmount, $dueAmount, $paymentStatus) {
            $invoice->update([
                'client_id' => $client->id,
                'client_name' => $client->name,
                'organization_name' => $validated['organization_name'] ?: $client->organization_name,
                'client_mobile' => $client->mobile,
                'client_email' => $validated['client_email'] ?: $client->email,
                'service_category' => $validated['service_category'],
                'service_type' => $validated['service_type'],
                'description' => $validated['description'] ?? null,
                'terms_type' => $validated['terms_type'],
                'terms_text' => $validated['terms_type'] === 'custom'
                    ? $validated['terms_text']
                    : self::DEFAULT_TERMS,
                'company_phone' => $validated['company_phone'] ?? $invoice->company_phone,
                'company_email' => $validated['company_email'] ?? $invoice->company_email,
                'company_address' => $validated['company_address'] ?? $invoice->company_address,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'vat_rate' => 0,
                'vat_amount' => $vatAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'payment_status' => $paymentStatus,
                'payment_date' => $validated['payment_date'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
            ]);

            // Delete existing items and create new ones
            $invoice->items()->delete();
            foreach ($items as $index => $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'sl' => $index + 1,
                    'service_description' => $item['service_description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_type' => $item['discount_type'],
                    'discount_value' => $item['discount_value'],
                    'discount_amount' => $item['discount_amount'],
                    'vat_rate' => $item['vat_rate'],
                    'vat_amount' => $item['vat_amount'],
                    'line_total' => $item['line_total'],
                ]);
            }
        });

        $invoice->load(['client', 'items', 'creator']);

        // Regenerate PDF
        $pdfPath = $this->storePdf($invoice);
        $invoice->update(['pdf_path' => $pdfPath]);

        return redirect()
            ->route('invoices.index')
            ->with('success', 'Invoice updated successfully.');
    }


    public function show(Invoice $invoice)
    {
        $invoice->load(['client', 'items', 'creator']);

        return Inertia::render('Invoices/Show', [
            'invoice' => [
                'id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'invoice_date' => $invoice->invoice_date?->format('Y-m-d'),
                'client_name' => $invoice->client_name,
                'organization_name' => $invoice->organization_name,
                'client_mobile' => $invoice->client_mobile,
                'client_email' => $invoice->client_email,
                'service_category' => $invoice->service_category,
                'service_type' => $invoice->service_type,
                'description' => $invoice->description,
                'terms_type' => $invoice->terms_type ?? 'default',
                'terms_text' => $invoice->terms_text ?? self::DEFAULT_TERMS,
                'company_phone' => $invoice->company_phone,
                'company_email' => $invoice->company_email,
                'company_address' => $invoice->company_address,
                'subtotal' => (float) $invoice->subtotal,
                'discount_amount' => (float) $invoice->discount_amount,
                'vat_amount' => (float) $invoice->vat_amount,
                'total_amount' => (float) $invoice->total_amount,
                'paid_amount' => (float) $invoice->paid_amount,
                'due_amount' => (float) $invoice->due_amount,
                'payment_status' => $invoice->payment_status,
                'payment_date' => $invoice->payment_date?->format('Y-m-d'),
                'payment_method' => $invoice->payment_method,
                'created_by' => $invoice->creator?->name,
                'pdf_path' => $invoice->pdf_path,
                'items' => $invoice->items->map(fn ($item) => [
                    'id' => $item->id,
                    'sl' => $item->sl,
                    'service_description' => $item->service_description,
                    'quantity' => (float) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'discount_type' => $item->discount_type ?? 'percent',
                    'discount_value' => (float) ($item->discount_value ?? 0),
                    'discount_amount' => (float) ($item->discount_amount ?? 0),
                    'vat_rate' => (float) ($item->vat_rate ?? 0),
                    'vat_amount' => (float) ($item->vat_amount ?? 0),
                    'line_total' => (float) $item->line_total,
                ]),
            ],
        ]);
    }

    public function download(Invoice $invoice)
    {
        if (! $invoice->pdf_path || ! Storage::disk('public')->exists($invoice->pdf_path)) {
            $invoice->load(['client', 'items', 'creator']);
            $pdfPath = $this->storePdf($invoice);
            $invoice->update(['pdf_path' => $pdfPath]);
        }

        return Storage::disk('public')->download(
            $invoice->pdf_path,
            $invoice->invoice_no . '.pdf'
        );
    }

    private function storePdf(Invoice $invoice): string
    {
        $path = 'invoices/' . $invoice->invoice_date->format('Y/m') . '/' . $invoice->invoice_no . '.pdf';

        $pdf = Pdf::loadView('pdfs.invoice_report', [
            'invoice' => $invoice,
            'defaultTerms' => self::DEFAULT_TERMS,
        ])->setPaper('A4');

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }
}
