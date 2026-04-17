<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Generation;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = Cache::remember('admin:dashboard:stats', 300, fn () => [
            'total_users'        => User::count(),
            'total_generations'  => Generation::count(),
            'completed'          => Generation::where('status', 'completed')->count(),
            'failed'             => Generation::where('status', 'failed')->count(),
            'pending'            => Generation::whereIn('status', ['pending', 'processing'])->count(),
            'total_credits_used' => Generation::sum('credits_used'),
        ]);

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recent_generations' => Generation::with('user:id,name,email')
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }
}
