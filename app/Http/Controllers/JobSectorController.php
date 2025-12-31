<?php

namespace App\Http\Controllers;

use App\Models\JobSector;
use Illuminate\Http\Request;

class JobSectorController extends Controller
{
    /**
     * Store a new job sector
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:job_sectors,id',
            'description' => 'nullable|string|max:500',
        ]);

        // Check if job sector already exists
        $exists = JobSector::where('name', $validated['name'])
            ->where('parent_id', $validated['parent_id'] ?? null)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'This job sector already exists.',
            ], 422);
        }

        $jobSector = JobSector::create($validated);

        return response()->json([
            'message' => 'Job sector added successfully!',
            'jobSector' => [
                'id' => $jobSector->id,
                'name' => $jobSector->name,
                'parent_id' => $jobSector->parent_id,
            ],
        ], 201);
    }
}
