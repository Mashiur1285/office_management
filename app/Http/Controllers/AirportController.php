<?php

namespace App\Http\Controllers;

use App\Models\Airport;
use Illuminate\Http\Request;

class AirportController extends Controller
{
    public function index(Request $request)
    {
        $query = Airport::query()->orderBy('country')->orderBy('name');

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%");
            });
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'    => 'required|string|max:10|unique:airports,code',
            'name'    => 'required|string|max:255',
            'country' => 'nullable|string|max:255',
        ]);

        $airport = Airport::create($data);

        return response()->json($airport, 201);
    }

    public function update(Request $request, Airport $airport)
    {
        $data = $request->validate([
            'code'    => 'required|string|max:10|unique:airports,code,' . $airport->id,
            'name'    => 'required|string|max:255',
            'country' => 'nullable|string|max:255',
        ]);

        $airport->update($data);

        return response()->json($airport);
    }

    public function destroy(Airport $airport)
    {
        $airport->delete();

        return response()->json(['ok' => true]);
    }
}
