<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TokenController extends Controller
{
    public function index(Request $request)
    {
        $tokens = $request->user()
            ->tokens()
            ->select(['id', 'name', 'last_used_at', 'created_at'])
            ->get();

        return Inertia::render('Profile/ApiTokens', [
            'tokens' => $tokens,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $token = $request->user()->createToken($validated['name']);

        return back()->with('token', $token->plainTextToken);
    }

    public function destroy(Request $request, int $tokenId)
    {
        $request->user()->tokens()->where('id', $tokenId)->delete();

        return back()->with('success', 'Token revoked.');
    }
}
