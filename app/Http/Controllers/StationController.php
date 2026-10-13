<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StationController extends Controller
{
    /**
     * Current logged-in user's station (for syncing the modal on open).
     */
    public function show()
    {
        $user = Auth::user();

        return response()->json([
            'user_id' => $user->id,
            'name'    => trim(($user->fname ?? '') . ' ' . ($user->lname ?? '')),
            'station' => $user->station,
        ]);
    }

    /**
     * Update the logged-in user's own station (Office / Field / Leave).
     * Broadcasts realtime so every open page updates without reload.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'station' => 'required|in:Office,Field,Leave',
        ]);

        $user = Auth::user();
        $user->station = $validated['station'];
        $user->save();

        $name = trim(($user->fname ?? '') . ' ' . ($user->lname ?? '')) ?: $user->email;

        broadcast(new \App\Events\UserStationUpdated($user->id, $name, $user->station));

        return response()->json([
            'success' => true,
            'message' => 'Station updated to ' . $user->station . '.',
            'data'    => [
                'user_id' => $user->id,
                'station' => $user->station,
            ],
        ]);
    }
}
