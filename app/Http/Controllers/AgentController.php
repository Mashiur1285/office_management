<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Arr;

class AgentController extends Controller
{
    public function index()
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

        Agent::create($data);

        return redirect()->route('agents.index')->with('success', 'Agent created successfully.');
    }

    public function show(Agent $agent)
    {
        $agent->load('clients:id,agent_id,name,passport_number,nid_number');

        return Inertia::render('Agents/Show', [
            'agent' => [
                'id' => $agent->id,
                'name' => $agent->name,
                'mobile' => $agent->mobile,
                'district' => $agent->district,
                'address' => $agent->address,
                'bank_details' => $agent->bank_details,
                'services' => $agent->services ?? [],
                'nid_file_path' => $agent->nid_file_path,
                'clients_count' => $agent->clients->count(),
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
