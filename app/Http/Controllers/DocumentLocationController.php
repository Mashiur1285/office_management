<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\BdCompany;
use App\Models\Client;
use App\Models\DocumentLocation;
use App\Models\DocumentLocationHistory;
use App\Models\ForeignCompany;
use App\Models\OfficeStaff;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class DocumentLocationController extends Controller
{
    public function show(Client $client)
    {
        $current = DocumentLocation::with('holder')->where('client_id', $client->id)->latest()->first();
        $history = DocumentLocationHistory::with(['fromHolder', 'toHolder'])
            ->where('client_id', $client->id)
            ->orderByDesc('moved_at')
            ->limit(50)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'from_holder_type' => $item->from_holder_type,
                    'from_holder_name' => $item->from_holder_name,
                    'to_holder_type' => $item->to_holder_type,
                    'to_holder_name' => $item->to_holder_name,
                    'moved_at' => $item->moved_at?->toDateTimeString(),
                    'notes' => $item->notes,
                ];
            });

        $holders = [
            ['value' => 'agency_user', 'label' => 'Agency Staff Member'],
            ['value' => 'bd_company', 'label' => 'BD Processing Company'],
        ];

        $agencyUsers = OfficeStaff::select('id', 'name', 'email', 'designation')
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(function ($staff) {
                $label = $staff->name;
                if ($staff->designation) {
                    $label .= ' - ' . $staff->designation;
                }
                if ($staff->email) {
                    $label .= ' (' . $staff->email . ')';
                }
                return [
                    'value' => $staff->id,
                    'label' => $label,
                ];
            });

        $agents = Agent::select('id', 'name', 'mobile')->get()->map(function ($agent) {
            return [
                'value' => $agent->id,
                'label' => $agent->name . ($agent->mobile ? ' (' . $agent->mobile . ')' : ''),
            ];
        });

        $bdCompanies = BdCompany::select('id', 'name', 'contact_person_name')->get()->map(function ($company) {
            return [
                'value' => $company->id,
                'label' => $company->name . ($company->contact_person_name ? ' - ' . $company->contact_person_name : ''),
            ];
        });

        $foreignCompanies = ForeignCompany::select('id', 'name', 'country')->get()->map(function ($company) {
            return [
                'value' => $company->id,
                'label' => $company->name . ($company->country ? ' (' . $company->country . ')' : ''),
            ];
        });

        $statusOptions = [
            ['value' => 'pending', 'label' => 'Pending'],
            ['value' => 'accepted', 'label' => 'Accepted'],
            ['value' => 'rejected', 'label' => 'Rejected'],
            ['value' => 'completed', 'label' => 'Completed'],
        ];

        return Inertia::render('Clients/DocumentTracking', [
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'passport_number' => $client->passport_number,
                'visa_status' => $client->visa_status,
                'visa_stage' => $client->visa_stage,
                'visa_stage_label' => $client->getVisaStageLabel(),
                'visa_stage_badge_class' => $client->getVisaStageBadgeClass(),
            ],
            'current' => $current ? [
                'holder_type' => $current->holder_type,
                'holder_id' => $current->holder_id,
                'holder_name' => $current->holder_name,
                'holder_details' => $current->holder_details,
                'processing_status' => $current->processing_status,
                'processing_notes' => $current->processing_notes,
                'received_at' => optional($current->received_at)?->toDateTimeString(),
                'expected_return_at' => optional($current->expected_return_at)?->toDateString(),
                'returned_at' => optional($current->returned_at)?->toDateTimeString(),
                'notes' => $current->notes,
                'days_with_holder' => $current->received_at ? $current->received_at->diffInDays(now()) : null,
                'overdue' => $current->received_at ? $current->received_at->diffInDays(now()) > 8 && ! $current->returned_at : false,
            ] : null,
            'history' => $history,
            'holders' => $holders,
            'agencyUsers' => $agencyUsers,
            'agents' => $agents,
            'bdCompanies' => $bdCompanies,
            'foreignCompanies' => $foreignCompanies,
            'statusOptions' => $statusOptions,
        ]);
    }

    public function store(Request $request, Client $client)
    {
        $data = $request->validate([
            'to_holder_type' => ['required', Rule::in(['agency_user', 'bd_company'])],
            'to_holder_id' => ['nullable', 'integer'],
            'expected_return_at' => ['nullable', 'date'],
            'processing_status' => ['nullable', Rule::in(['pending', 'accepted', 'rejected', 'completed'])],
            'processing_notes' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $current = DocumentLocation::where('client_id', $client->id)->latest()->first();

        DocumentLocationHistory::create([
            'client_id' => $client->id,
            'from_holder_type' => $current?->holder_type,
            'from_holder_id' => $current?->holder_id,
            'to_holder_type' => $data['to_holder_type'],
            'to_holder_id' => $data['to_holder_id'] ?? null,
            'moved_at' => now(),
            'notes' => $data['notes'] ?? null,
        ]);

        DocumentLocation::updateOrCreate(
            ['client_id' => $client->id],
            [
                'holder_type' => $data['to_holder_type'],
                'holder_id' => $data['to_holder_id'] ?? null,
                'processing_status' => $data['processing_status'] ?? 'pending',
                'processing_notes' => $data['processing_notes'] ?? null,
                'received_at' => now(),
                'expected_return_at' => $data['expected_return_at'] ?? null,
                'returned_at' => null,
                'notes' => $data['notes'] ?? null,
            ]
        );

        return redirect()->route('clients.documents.show', $client->id)->with('success', 'Document location updated successfully.');
    }

    public function updateStatus(Request $request, Client $client)
    {
        $data = $request->validate([
            'processing_status' => ['required', Rule::in(['pending', 'accepted', 'rejected', 'completed'])],
            'processing_notes' => ['nullable', 'string'],
        ]);

        $current = DocumentLocation::where('client_id', $client->id)->latest()->firstOrFail();

        $current->update([
            'processing_status' => $data['processing_status'],
            'processing_notes' => $data['processing_notes'] ?? $current->processing_notes,
        ]);

        return redirect()->route('clients.documents.show', $client->id)->with('success', 'Processing status updated successfully.');
    }
}
