<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class NotepadController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Get or create notepad for user
        $notepad = $user->notepad()->firstOrCreate([
            'user_id' => $user->id,
        ]);

        return Inertia::render('Notepad/Index', [
            'notepad' => [
                'id' => $notepad->id,
                'has_password' => !empty($notepad->password_hash),
                'last_unlocked_at' => $notepad->last_unlocked_at,
                'updated_at' => $notepad->updated_at,
            ],
        ]);
    }

    public function setup(Request $request)
    {
        $request->validate([
            'password' => ['required', 'regex:/^\d{4}$/'],
        ]);

        $user = auth()->user();
        $notepad = $user->notepad()->firstOrCreate([
            'user_id' => $user->id,
        ]);

        $notepad->update([
            'password_hash' => Hash::make($request->password),
            'last_unlocked_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'content' => $notepad->content ?? '',
            'updated_at' => $notepad->updated_at,
        ]);
    }

    public function unlock(Request $request)
    {
        $request->validate([
            'password' => ['required', 'regex:/^\d{4}$/'],
        ]);

        $user = auth()->user();

        $notepad = $user->notepad()->firstOrCreate([
            'user_id' => $user->id,
        ]);

        if (empty($notepad->password_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Notepad password not set',
            ], 409);
        }

        if (!Hash::check($request->password, $notepad->password_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password',
            ], 401);
        }

        // Update last unlocked timestamp
        $notepad->update([
            'last_unlocked_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'content' => $notepad->content ?? '',
            'updated_at' => $notepad->updated_at,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'content' => 'nullable|string',
        ]);

        $user = auth()->user();
        $notepad = $user->notepad()->firstOrCreate([
            'user_id' => $user->id,
        ]);

        $notepad->update([
            'content' => $request->content,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notepad saved successfully',
            'updated_at' => $notepad->updated_at,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'account_password' => ['required', 'string'],
            'new_password' => ['required', 'regex:/^\d{4}$/'],
        ]);

        $user = auth()->user();

        if (!Hash::check($request->account_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid account password',
            ], 401);
        }

        $notepad = $user->notepad()->firstOrCreate([
            'user_id' => $user->id,
        ]);

        $notepad->update([
            'password_hash' => Hash::make($request->new_password),
            'last_unlocked_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'content' => $notepad->content ?? '',
            'updated_at' => $notepad->updated_at,
        ]);
    }
}
