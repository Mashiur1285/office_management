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
use Spatie\LaravelPdf\Facades\Pdf;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with(['client:id,name', 'creator:id,name'])
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
            ->groupBy('category')
            ->map(fn ($items) => $items->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
            ])->values())
            ->toArray();

        return Inertia::render('Invoices/Create', [
            'clients' => $clients,
            'subcategories' => $subcategories,
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
            'description' => ['nullable', 'string'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_address' => ['nullable', 'string'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'vat_rate' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_date' => ['nullable', 'date'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.service_description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $client = Client::findOrFail($validated['client_id']);
        $items = collect($validated['items'])->values();
        $subtotal = $items->sum(fn ($item) => (float) $item['quantity'] * (float) $item['unit_price']);
        $discountAmount = (float) ($validated['discount_amount'] ?? 0);
        $taxableAmount = max(0, $subtotal - $discountAmount);
        $vatRate = (float) ($validated['vat_rate'] ?? 0);
        $vatAmount = ($taxableAmount * $vatRate) / 100;
        $totalAmount = $taxableAmount + $vatAmount;
        $paidAmount = (float) ($validated['paid_amount'] ?? 0);

        if ($paidAmount > $totalAmount) {
            return back()
                ->withErrors(['paid_amount' => 'Paid amount cannot exceed total amount.'])
                ->withInput();
        }

        $dueAmount = max(0, $totalAmount - $paidAmount);
        $paymentStatus = $paidAmount <= 0 ? 'unpaid' : ($dueAmount <= 0 ? 'paid' : 'partial');

        $invoice = DB::transaction(function () use ($validated, $client, $items, $subtotal, $discountAmount, $vatRate, $vatAmount, $totalAmount, $paidAmount, $dueAmount, $paymentStatus) {
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
                'company_phone' => $validated['company_phone'] ?? null,
                'company_email' => $validated['company_email'] ?? null,
                'company_address' => $validated['company_address'] ?? null,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'vat_rate' => $vatRate,
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
                $lineTotal = (float) $item['quantity'] * (float) $item['unit_price'];
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'sl' => $index + 1,
                    'service_description' => $item['service_description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $lineTotal,
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
                'company_phone' => $invoice->company_phone,
                'company_email' => $invoice->company_email,
                'company_address' => $invoice->company_address,
                'subtotal' => (float) $invoice->subtotal,
                'discount_amount' => (float) $invoice->discount_amount,
                'vat_rate' => (float) $invoice->vat_rate,
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

        Pdf::view('pdfs.invoice_report', [
            'invoice' => $invoice,
        ])
            ->format('A4')
            ->disk('public', 'public')
            ->save($path);

        return $path;
    }
}
