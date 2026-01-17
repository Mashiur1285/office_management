<?php

namespace App\Http\Controllers;

use App\Models\DocumentHolderType;
use App\Models\DocumentLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class DocumentHolderTypeController extends Controller
{
    public function index()
    {
        return response()->json(
            DocumentHolderType::query()
                ->orderByDesc('is_system')
                ->orderBy('label')
                ->get()
        );
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Adding holder types is disabled.'], 403);

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
        ]);

        $value = $this->makeValue($validated['label']);

        $holderType = DocumentHolderType::create([
            'value' => $value,
            'label' => $validated['label'],
            'is_system' => false,
        ]);

        Cache::forget("document_holder_type_label_{$holderType->value}");

        return response()->json($holderType, 201);
    }

    public function update(Request $request, DocumentHolderType $documentHolderType)
    {
        return response()->json(['message' => 'Editing holder types is disabled.'], 403);

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
        ]);

        $documentHolderType->update([
            'label' => $validated['label'],
        ]);

        Cache::forget("document_holder_type_label_{$documentHolderType->value}");

        return response()->json($documentHolderType);
    }

    public function destroy(DocumentHolderType $documentHolderType)
    {
        return response()->json(['message' => 'Deleting holder types is disabled.'], 403);

        $inUse = DocumentLocation::query()
            ->where('holder_type', $documentHolderType->value)
            ->exists();

        if ($inUse) {
            return response()->json(['message' => 'This holder type is in use and cannot be deleted.'], 422);
        }

        $documentHolderType->delete();

        Cache::forget("document_holder_type_label_{$documentHolderType->value}");

        return response()->json(['message' => 'Holder type deleted successfully.']);
    }

    private function makeValue(string $label): string
    {
        $base = Str::slug($label, '_');

        if ($base === '') {
            $base = 'holder_type';
        }

        $value = $base;
        $counter = 2;

        while (DocumentHolderType::query()->where('value', $value)->exists()) {
            $value = $base . '_' . $counter;
            $counter++;
        }

        return $value;
    }
}
