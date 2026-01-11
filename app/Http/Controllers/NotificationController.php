<?php

namespace App\Http\Controllers;

use App\Models\DocumentLocation;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function documentOverdue(): JsonResponse
    {
        $locations = DocumentLocation::with('client:id,name')
            ->where('holder_type', 'bd_company')
            ->whereNotNull('received_at')
            ->whereNull('returned_at')
            ->whereNotIn('processing_status', ['accepted', 'rejected'])
            ->get();

        $items = $locations->filter(function ($location) {
            $days = $location->received_at?->diffInDays(Carbon::now());
            return $days !== null && $days > 8;
        })->map(function ($location) {
            $days = $location->received_at?->diffInDays(Carbon::now());
            return [
                'client_id' => $location->client_id,
                'client_name' => $location->client?->name ?? 'Unknown',
                'days' => $days !== null ? (int) $days : null,
                'received_at' => $location->received_at?->toDateString(),
            ];
        })->values();

        return response()->json([
            'count' => $items->count(),
            'items' => $items,
        ]);
    }
}
