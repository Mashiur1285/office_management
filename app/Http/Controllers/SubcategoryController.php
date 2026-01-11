<?php

namespace App\Http\Controllers;

use App\Models\Subcategory;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Subcategory::query();

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $subcategories = $query->orderBy('name')->get();

        return response()->json($subcategories);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'category' => 'required|string',
            'name' => 'required|string|max:255',
            'vat_rate' => 'required|numeric|min:0|max:100',
        ]);

        $subcategory = Subcategory::create($validated);

        return response()->json($subcategory, 201);
    }

    public function update(Request $request, Subcategory $subcategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'vat_rate' => 'required|numeric|min:0|max:100',
        ]);

        $subcategory->update($validated);

        return response()->json($subcategory);
    }

    public function destroy(Subcategory $subcategory)
    {
        $subcategory->delete();

        return response()->json(['message' => 'Subcategory deleted successfully']);
    }
}
