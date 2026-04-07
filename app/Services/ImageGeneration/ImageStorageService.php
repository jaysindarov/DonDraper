<?php

namespace App\Services\ImageGeneration;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Handles persisting generated image data to the public storage disk
 * and returning the publicly accessible URL.
 */
class ImageStorageService
{
    public function storeFromBase64(int $generationId, string $b64): string
    {
        $path = $this->storagePath($generationId);
        Storage::disk('public')->put($path, base64_decode($b64));

        return Storage::disk('public')->url($path);
    }

    public function storeFromUrl(int $generationId, string $url): string
    {
        $body = Http::timeout(30)->get($url)->body();
        $path = $this->storagePath($generationId);
        Storage::disk('public')->put($path, $body);

        return Storage::disk('public')->url($path);
    }

    private function storagePath(int $generationId): string
    {
        return "generations/{$generationId}.png";
    }
}
