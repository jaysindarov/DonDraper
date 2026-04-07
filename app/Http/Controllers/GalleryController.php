<?php

namespace App\Http\Controllers;

use App\Models\Generation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        $generations = Generation::where('is_public', true)
            ->where('status', 'completed')
            ->with('user:id,name,avatar')
            ->when($request->type, fn ($q, $t) => $q->where('type', $t))
            ->latest()
            ->paginate(24)
            ->withQueryString();

        return Inertia::render('Gallery/Index', [
            'generations' => $generations,
            'filters'     => $request->only('type'),
        ]);
    }
}
