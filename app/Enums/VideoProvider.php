<?php

declare(strict_types=1);

namespace App\Enums;

enum VideoProvider: string
{
    case OpenAi      = 'openai';
    case ElevenLabs  = 'elevenlabs';
    case Google      = 'google';

    public function label(): string
    {
        return match ($this) {
            self::OpenAi     => 'OpenAI Sora',
            self::ElevenLabs => 'ElevenLabs Video',
            self::Google     => 'Google Veo 3.1',
        };
    }

    public static function fromModel(string $model): self
    {
        return match (true) {
            str_starts_with($model, 'sora')       => self::OpenAi,
            str_starts_with($model, 'elevenlabs') => self::ElevenLabs,
            str_starts_with($model, 'veo')        => self::Google,
            default => throw new \InvalidArgumentException("Unknown video model: {$model}"),
        };
    }
}
