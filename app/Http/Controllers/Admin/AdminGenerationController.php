<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Generation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        // Clean up product images
        foreach ($generation->allProductImagePaths() as $path) {
            Storage::disk('public')->delete($path);
        }

        // Clean up reference person images
        foreach ($generation->reference_persons ?? [] as $person) {
            if (!empty($person['path'])) {
                Storage::disk('public')->delete($person['path']);
            }
        }

        // Clean up result file
        if ($generation->result_url) {
            $relativePath = ltrim(parse_url($generation->result_url, PHP_URL_PATH), '/');
            $diskPath = preg_replace('#^storage/#', '', $relativePath);
            if (Storage::disk('public')->exists($diskPath)) {
                Storage::disk('public')->delete($diskPath);
            }
        }

        $generation->delete();

        return back()->with('success', 'Generation deleted.');
    }
}
