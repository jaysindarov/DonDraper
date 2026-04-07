<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\VideoGenerationProvider;
use App\Services\VideoGeneration\ElevenLabsVideoProvider;
use App\Services\VideoGeneration\GoogleVeoProvider;
use App\Services\VideoGeneration\OpenAiSoraProvider;
use App\Auth\CustomSessionGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind all video providers as tagged implementations of the contract.
        // ProcessVideoGeneration receives them via variadic DI: handle(VideoGenerationProvider ...$providers)
        $this->app->tag([
            OpenAiSoraProvider::class,
            ElevenLabsVideoProvider::class,
            GoogleVeoProvider::class,
        ], VideoGenerationProvider::class);

        $this->app->bind(VideoGenerationProvider::class, OpenAiSoraProvider::class);
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Replace the default session guard with our custom one so remember-me
        // cookies expire after 1 day instead of Laravel's default ~5 years.
        Auth::extend('session', function ($app, $name, array $config) {
            $guard = new CustomSessionGuard(
                $name,
                Auth::createUserProvider($config['provider']),
                $app['session.store'],
                $app['request'],
            );

            $guard->setCookieJar($app['cookie']);
            $app->refresh('request', $guard, 'setRequest');

            return $guard;
        });
    }
}
