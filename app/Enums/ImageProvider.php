<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Canonical list of image providers supported by the platform.
 *
 * The value is the string stored in generations.provider and used
 * to identify the provider throughout the codebase — keep it lowercase.
 *
 * To add a new provider:
 *   1. Add a case here.
 *   2. Set the matching 'provider' key in config/ai_models.php.
 *   3. No controller changes required — fromModel() resolves it automatically.
 */
enum ImageProvider: string
{
    case OpenAi = 'openai';
    case Google = 'google';
    case XAi    = 'xai';

    public function label(): string
    {
        return match ($this) {
            self::OpenAi => 'OpenAI',
            self::Google => 'Google',
            self::XAi    => 'xAI',
        };
    }

    /**
     * Resolve the image provider enum from a model key in config/ai_models.php.
     *
     * @throws \InvalidArgumentException  When the model is unknown or its provider is not registered.
     */
    public static function fromModel(string $model): self
    {
        $providerLabel = config("ai_models.{$model}.provider")
            ?? throw new \InvalidArgumentException("Unknown image model \"{$model}\". Check config/ai_models.php.");

        return match (strtolower((string) $providerLabel)) {
            'openai' => self::OpenAi,
            'google' => self::Google,
            'xai'    => self::XAi,
            default  => throw new \InvalidArgumentException(
                "Unknown image provider \"{$providerLabel}\" for model \"{$model}\". Register it in App\\Enums\\ImageProvider."
            ),
        };
    }
}
