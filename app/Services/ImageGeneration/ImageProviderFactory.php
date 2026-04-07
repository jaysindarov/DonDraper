<?php

namespace App\Services\ImageGeneration;

use App\Contracts\ImageGenerationProvider;

/**
 * Resolves the correct ImageGenerationProvider for a given model key.
 *
 * Provider classes are resolved via the Laravel container so their
 * own dependencies (e.g. ImageStorageService) are injected automatically.
 *
 * To support a new model, add an entry to config/ai_models.php — no code change here.
 */
class ImageProviderFactory
{
    /**
     * @throws \InvalidArgumentException  When the model is not in config/ai_models.php
     */
    public function make(string $model): ImageGenerationProvider
    {
        $config = config("ai_models.{$model}");

        if (!$config) {
            throw new \InvalidArgumentException(
                "Unknown AI model \"{$model}\". Add it to config/ai_models.php."
            );
        }

        /** @var ImageGenerationProvider */
        return app($config['driver']);
    }

    /**
     * Return the raw config array for a model.
     *
     * @return array<string, mixed>
     * @throws \InvalidArgumentException
     */
    public function configFor(string $model): array
    {
        $config = config("ai_models.{$model}");

        if (!$config) {
            throw new \InvalidArgumentException(
                "Unknown AI model \"{$model}\". Add it to config/ai_models.php."
            );
        }

        return $config;
    }
}
