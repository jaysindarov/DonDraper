<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     *
     * After saving, all other active sessions are deleted from the database
     * and all remember-me cookies are invalidated by rotating remember_token.
     * The current session is kept so the user stays logged in.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = $request->user();

        $user->forceFill([
            'password'       => Hash::make($validated['password']),
            'remember_token' => Str::random(60), // invalidates all remember-me cookies
        ])->save();

        // Delete every other active session for this user
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', $request->session()->getId())
            ->delete();

        return back()->with('status', 'Password updated. All other devices have been logged out.');
    }
}
