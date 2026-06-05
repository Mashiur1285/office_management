<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Arr;
use Barryvdh\DomPDF\Facade\Pdf;

class AgentController extends Controller
{
    public function index(Request $request)
    {
        $agents = Agent::query()
            ->withCount('clients')
            ->latest()
            ->get()
            ->map(function (Agent $agent) {
                return [
                    'id' => $agent->id,
                    'name' => $agent->name,
                    'mobile' => $agent->mobile,
                    'district' => $agent->district,
                    'services' => $agent->services ?? [],
                    'clients_count' => $agent->clients_count,
                ];
            });

        return Inertia::render('Agents/Index', [
            'agents' => $agents,
            'readOnly' => $request->routeIs('database.*'),
        ]);
    }

    public function export(Request $request, ?string $type = null)
    {
        $agents = Agent::query()
            ->withCount('clients')
            ->latest()
            ->get();

        if ($type === 'pdf') {
            $fileName = 'agents-report-' . now()->format('Y-m-d') . '.pdf';

            return Pdf::loadView('pdfs.agent_list_report', [
                'agents' => $agents,
            ])->download($fileName);
        }

        $handle = fopen('php://memory', 'w');

        fputcsv($handle, [
            'Name',
            'Mobile',
            'District',
            'Services',
            'Clients',
        ]);

        foreach ($agents as $agent) {
            fputcsv($handle, [
                $agent->name,
                $agent->mobile,
                $agent->district,
                implode(', ', $agent->services ?? []),
                $agent->clients_count,
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $fileName = 'agents-report-' . now()->format('Y-m-d') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function create()
    {
        $services = ['Visa Processing', 'Medical', 'Job Offer Letter', 'Other'];

        return Inertia::render('Agents/Create', [
            'services' => $services,
            'agent' => null,
            'mode' => 'create',
        ]);
    }

    public function edit(Agent $agent)
    {
        $services = ['Visa Processing', 'Medical', 'Job Offer Letter', 'Other'];

        return Inertia::render('Agents/Create', [
            'services' => $services,
            'agent' => $agent->only([
                'id',
                'name',
                'nid_number',
                'district',
                'bank_details',
                'mobile',
                'address',
                'services',
            ]),
            'mode' => 'edit',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nid_number' => ['nullable', 'string', 'max:100'],
            'nid_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'district' => ['nullable', 'string', 'max:255'],
            'bank_details' => ['nullable', 'string'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'services' => ['nullable', 'array'],
        ]);

        if ($request->hasFile('nid_file')) {
            $data['nid_file_path'] = $request->file('nid_file')->store('agents/nid', 'public');
        }

        $data['services'] = $data['services'] ?? [];

        $agent = Agent::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'agent' => $agent->only(['id', 'name', 'mobile', 'district', 'address']),
            ], 201);
        }

        return redirect()->route('agents.index')->with('success', 'Agent created successfully.');
    }

    public function show(Request $request, Agent $agent)
    {
        $agent->load('clients:id,agent_id,name,passport_number,nid_number');

        $totalReceived = (float) \App\Models\Payment::where('agent_id', $agent->id)
            ->where('type', 'payment')
            ->sum('amount');

        $totalRefunded = (float) \App\Models\Payment::where('agent_id', $agent->id)
            ->where('type', 'refund')
            ->sum('amount');

        $refunds = \App\Models\Payment::with(['payable', 'creator:id,name'])
            ->where('agent_id', $agent->id)
            ->where('type', 'refund')
            ->latest('payment_date')
            ->latest('id')
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'payment_date' => $payment->payment_date?->format('Y-m-d'),
                    'client_name' => $payment->payable?->name ?? '—',
                    'amount' => (float) $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'notes' => $payment->notes,
                    'created_by' => $payment->creator?->name,
                ];
            });

        $agent->load('user');

        return Inertia::render('Agents/Show', [
            'agent' => [
                'id'             => $agent->id,
                'name'           => $agent->name,
                'mobile'         => $agent->mobile,
                'district'       => $agent->district,
                'address'        => $agent->address,
                'bank_details'   => $agent->bank_details,
                'services'       => $agent->services ?? [],
                'nid_file_path'  => $agent->nid_file_path,
                'clients_count'  => $agent->clients->count(),
                'total_received' => $totalReceived,
                'total_refunded' => $totalRefunded,
                'has_account'    => (bool) $agent->user_id,
                'account_email'  => $agent->user?->email,
            ],
            'clients' => $agent->clients->map(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'passport_number' => $client->passport_number,
                    'nid_number' => $client->nid_number,
                    'documents_link' => route('clients.documents.show', $client),
                ];
            }),
            'refunds' => $refunds,
            'readOnly' => $request->routeIs('database.*'),
        ]);
    }

    public function update(Request $request, Agent $agent)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nid_number' => ['nullable', 'string', 'max:100'],
            'nid_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'district' => ['nullable', 'string', 'max:255'],
            'bank_details' => ['nullable', 'string'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'services' => ['nullable', 'array'],
        ]);

        if ($request->hasFile('nid_file')) {
            $data['nid_file_path'] = $request->file('nid_file')->store('agents/nid', 'public');
        }

        $data = Arr::except($data, ['nid_file']);
        $data['services'] = $data['services'] ?? [];

        $agent->update($data);

        return redirect()->route('agents.index')->with('success', 'Agent updated successfully.');
    }
}
