<?php

use App\Http\Controllers\GenerationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard', [
            'recentGenerations' => auth()->user()->generations()->latest()->limit(8)->get(),
            'stats' => [
                'total' => auth()->user()->generations()->count(),
                'completed' => auth()->user()->generations()->where('status', 'completed')->count(),
                'credits' => auth()->user()->credits,
            ],
        ]);
    })->name('dashboard');

    Route::resource('generations', GenerationController::class)
        ->only(['index', 'create', 'store', 'show', 'destroy']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
