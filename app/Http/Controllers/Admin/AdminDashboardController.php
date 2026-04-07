<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Generation;
use App\Models\User;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_users'       => User::count(),
                'total_generations' => Generation::count(),
                'completed'         => Generation::where('status', 'completed')->count(),
                'failed'            => Generation::where('status', 'failed')->count(),
                'pending'           => Generation::whereIn('status', ['pending', 'processing'])->count(),
                'total_credits_used' => Generation::sum('credits_used'),
            ],
            'recent_generations' => Generation::with('user:id,name,email')
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }
}
