<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RefundReportController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $perPage = (int) $request->input('per_page', 20);
        $perPage = $perPage > 0 ? $perPage : 20;

        $refunds = Payment::with(['payable', 'agent', 'creator'])
            ->where('type', 'refund')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('notes', 'like', "%{$search}%")
                        ->orWhere('payment_method', 'like', "%{$search}%")
                        ->orWhereHas('payable', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('phone_number', 'like', "%{$search}%");
                        })
                        ->orWhereHas('agent', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($startDate, fn($q) => $q->whereDate('payment_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('payment_date', '<=', $endDate))
            ->latest('payment_date')
            ->latest('id')
            ->paginate($perPage)
            ->through(function ($refund) {
                $clientName = '';
                $clientPhone = '';

                if ($refund->payable) {
                    if ($refund->payable_type === 'App\\Models\\Client') {
                        $clientName = $refund->payable->name ?? '';
                        $clientPhone = $refund->payable->phone_number ?? '';
                    }
                }

                // Determine who the refund was given to
                $refundedTo = '';
                $refundedToType = '';
                if ($refund->source === 'agent' && $refund->agent) {
                    $refundedTo = $refund->agent->name;
                    $refundedToType = 'Agent';
                } else {
                    $refundedTo = $clientName ?: ($refund->payable?->name ?? '—');
                    $refundedToType = 'Client';
                }

                return [
                    'id' => $refund->id,
                    'payment_date' => $refund->payment_date?->format('Y-m-d'),
                    'client_name' => $clientName,
                    'client_phone' => $clientPhone,
                    'refunded_to' => $refundedTo,
                    'refunded_to_type' => $refundedToType,
                    'source' => $refund->source,
                    'agent_name' => $refund->agent?->name,
                    'amount' => (float) $refund->amount,
                    'payment_method' => $refund->payment_method,
                    'notes' => $refund->notes,
                    'created_by' => $refund->creator?->name,
                ];
            });

        $totalRefund = (float) Payment::where('type', 'refund')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('notes', 'like', "%{$search}%")
                        ->orWhere('payment_method', 'like', "%{$search}%")
                        ->orWhereHas('payable', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('phone_number', 'like', "%{$search}%");
                        })
                        ->orWhereHas('agent', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($startDate, fn($q) => $q->whereDate('payment_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('payment_date', '<=', $endDate))
            ->sum('amount');

        return Inertia::render('Reports/RefundReport/Index', [
            'refunds' => $refunds,
            'totalRefund' => $totalRefund,
            'filters' => [
                'search' => $search,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    public function export(Request $request)
    {
        $type = $request->input('type');
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $refunds = Payment::with(['payable', 'agent', 'creator'])
            ->where('type', 'refund')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('notes', 'like', "%{$search}%")
                        ->orWhere('payment_method', 'like', "%{$search}%");
                });
            })
            ->when($startDate, fn($q) => $q->whereDate('payment_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('payment_date', '<=', $endDate))
            ->latest('payment_date')
            ->get();

        $fileName = 'refund_report_' . now()->format('Y_m_d_His');

        if ($type === 'pdf') {
            $totalRefund = (float) $refunds->sum('amount');

            return Pdf::loadView('pdfs.refund_report', [
                'refunds' => $refunds,
                'totalRefund' => $totalRefund,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])->download($fileName . '.pdf');
        }

        // CSV Export
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}.csv\"",
        ];

        $callback = function () use ($refunds) {
            $handle = fopen('php://output', 'w');

            // Header row
            fputcsv($handle, [
                'Date',
                'Client Name',
                'Phone',
                'Refunded To',
                'Type',
                'Source',
                'Agent',
                'Amount',
                'Method',
                'Notes',
                'Created By',
            ]);

            foreach ($refunds as $refund) {
                $clientName = '';
                $clientPhone = '';

                if ($refund->payable && $refund->payable_type === 'App\\Models\\Client') {
                    $clientName = $refund->payable->name ?? '';
                    $clientPhone = $refund->payable->phone_number ?? '';
                }

                $refundedTo = '';
                $refundedToType = '';
                if ($refund->source === 'agent' && $refund->agent) {
                    $refundedTo = $refund->agent->name;
                    $refundedToType = 'Agent';
                } else {
                    $refundedTo = $clientName ?: ($refund->payable?->name ?? '-');
                    $refundedToType = 'Client';
                }

                fputcsv($handle, [
                    $refund->payment_date?->format('Y-m-d'),
                    $clientName,
                    $clientPhone,
                    $refundedTo,
                    $refundedToType,
                    ucfirst($refund->source),
                    $refund->agent?->name ?? '-',
                    number_format($refund->amount, 2),
                    $refund->payment_method ?? '-',
                    $refund->notes ?? '-',
                    $refund->creator?->name ?? '-',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
