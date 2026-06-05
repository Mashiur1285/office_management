<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class AgentPortalController extends Controller
{
    public function dashboard()
    {
        return Inertia::render('AgentPortal/Dashboard');
    }

    public function data()
    {
        $agent = auth()->user()->agent;

        if (! $agent) {
            return response()->json(['error' => 'No agent profile linked'], 403);
        }

        $clients = $agent->clients()
            ->with(['bdCompany', 'foreignCompany', 'documentLocation'])
            ->latest()
            ->get()
            ->map(function ($client) {
                $holder   = $client->documentLocation;
                $status   = $this->resolveStatus($holder);
                $totalFee = (float) ($client->total_fee ?? 0);
                $due      = (float) ($client->current_due ?? 0);
                $paid     = max(0, $totalFee - $due);

                return [
                    'id'                      => $client->id,
                    'name'                    => $client->name,
                    'passport_number'         => $client->passport_number,
                    'job_sector'              => $client->job_sector,
                    'foreign_company'         => $client->foreignCompany?->name ?? $client->foreign_company_name ?? '—',
                    'bd_company'              => $client->bdCompany?->name ?? '—',
                    'status'                  => $status['label'],
                    'status_value'            => $status['value'],
                    'status_badge'            => $status['badge'],
                    'total_fee'               => $totalFee,
                    'paid'                    => $paid,
                    'due'                     => $due,
                    'expected_date_to_collect'=> $client->expected_date_to_collect?->format('d M Y') ?? '—',
                    'processing_status'       => $holder?->processing_status ?? '—',
                    'created_at'              => $client->created_at->format('d M Y'),
                ];
            });

        $summary = [
            'total'     => $clients->count(),
            'completed' => $clients->where('status_value', 'completed')->count(),
            'pending'   => $clients->whereIn('status_value', ['pending', 'processing'])->count(),
            'rejected'  => $clients->where('status_value', 'rejected')->count(),
            'total_fee' => $clients->sum('total_fee'),
            'total_paid'=> $clients->sum('paid'),
            'total_due' => $clients->sum('due'),
        ];

        return response()->json([
            'agent'    => ['id' => $agent->id, 'name' => $agent->name],
            'summary'  => $summary,
            'clients'  => $clients->values(),
        ]);
    }

    public function createAccount(Request $request, \App\Models\Agent $agent)
    {
        $request->validate([
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ]);

        $user = \App\Models\User::create([
            'name'     => $agent->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $user->assignRole('agent');
        $agent->update(['user_id' => $user->id]);

        return back()->with('success', 'Agent account created successfully.');
    }

    public function resetPassword(Request $request, \App\Models\Agent $agent)
    {
        $request->validate(['password' => 'required|min:8']);

        $agent->user?->update(['password' => bcrypt($request->password)]);

        return back()->with('success', 'Password updated successfully.');
    }

    private function resolveStatus($holder): array
    {
        if (! $holder || $holder->holder_type === 'agency' || $holder->holder_type === 'agency_user') {
            return ['value' => 'pending',    'label' => 'Pending at ZTTBL',  'badge' => 'bg-amber-100 text-amber-700'];
        }
        if ($holder->holder_type === 'bd_company') {
            return match ($holder->processing_status) {
                'accepted'  => ['value' => 'processing', 'label' => 'Vendor Accepted',  'badge' => 'bg-blue-100 text-blue-700'],
                'completed' => ['value' => 'completed',  'label' => 'Completed',         'badge' => 'bg-emerald-100 text-emerald-700'],
                'rejected'  => ['value' => 'rejected',   'label' => 'Rejected',          'badge' => 'bg-red-100 text-red-700'],
                default     => ['value' => 'pending',    'label' => 'Vendor Pending',    'badge' => 'bg-yellow-100 text-yellow-700'],
            };
        }
        if ($holder->holder_type === 'foreign_company') {
            return ['value' => 'processing', 'label' => 'At Foreign Company', 'badge' => 'bg-purple-100 text-purple-700'];
        }
        return ['value' => 'pending', 'label' => 'Pending', 'badge' => 'bg-gray-100 text-gray-700'];
    }
}
