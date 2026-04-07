<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%"))
            ->withCount('generations')
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users'  => $users,
            'search' => $request->search ?? '',
        ]);
    }

    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user->only('id', 'name', 'email', 'credits', 'plan', 'is_admin', 'created_at'),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'credits'  => 'required|integer|min:0',
            'plan'     => 'required|in:free,pro,enterprise',
            'is_admin' => 'required|boolean',
        ]);

        $user->update($validated);

        return back()->with('success', 'User updated.');
    }
}
