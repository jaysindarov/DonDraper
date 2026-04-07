<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Generation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminGenerationController extends Controller
{
    public function index(Request $request)
    {
        $generations = Generation::with('user:id,name,email')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->type, fn ($q, $t) => $q->where('type', $t))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Admin/Generations/Index', [
            'generations' => $generations,
            'filters'     => $request->only('status', 'type'),
        ]);
    }

    public function destroy(Generation $generation)
    {
        $generation->delete();

        return back()->with('success', 'Generation deleted.');
    }
}
