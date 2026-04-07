<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminGenerationController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\GenerationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PromptEnhancerController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return Inertia::render('Welcome', [
        'canLogin'    => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery');
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard', [
            'recentGenerations' => auth()->user()->generations()->latest()->limit(8)->get(),
            'stats' => [
                'total'     => auth()->user()->generations()->count(),
                'completed' => auth()->user()->generations()->where('status', 'completed')->count(),
                'credits'   => auth()->user()->credits,
            ],
        ]);
    })->name('dashboard');

    // Generations
    Route::resource('generations', GenerationController::class)
        ->only(['index', 'create', 'store', 'show', 'destroy']);

    Route::get('/generations/{generation}/download', [GenerationController::class, 'download'])
        ->name('generations.download');

    Route::patch('/generations/{generation}/toggle-public', [GenerationController::class, 'togglePublic'])
        ->name('generations.togglePublic');

    // Prompt enhancer
    Route::post('/prompt/enhance', [PromptEnhancerController::class, 'enhance'])
        ->name('prompt.enhance');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // API tokens
    Route::get('/api-tokens', [TokenController::class, 'index'])->name('api-tokens.index');
    Route::post('/api-tokens', [TokenController::class, 'store'])->name('api-tokens.store');
    Route::delete('/api-tokens/{tokenId}', [TokenController::class, 'destroy'])->name('api-tokens.destroy');

    // Teams (Enterprise)
    Route::middleware('plan:enterprise')->group(function () {
        Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
        Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
        Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
        Route::post('/teams/{team}/switch', [TeamController::class, 'switch'])->name('teams.switch');
        Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');
        Route::post('/teams/{team}/invite', [TeamController::class, 'invite'])->name('teams.invite');
    });
    Route::get('/invitations/{token}/accept', [TeamController::class, 'acceptInvite'])
        ->name('teams.invitation.accept');

    // Admin
    Route::middleware('is_admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::get('/generations', [AdminGenerationController::class, 'index'])->name('generations.index');
        Route::delete('/generations/{generation}', [AdminGenerationController::class, 'destroy'])->name('generations.destroy');
    });
});

require __DIR__.'/auth.php';
