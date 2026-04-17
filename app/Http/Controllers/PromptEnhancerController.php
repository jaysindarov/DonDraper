<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PromptEnhancerController extends Controller
{
    public function enhance(Request $request)
    {
        $validated = $request->validate([
            'prompt' => 'required|string|max:2000',
            'type'   => 'required|in:image,video',
        ]);

        $systemPrompt = $validated['type'] === 'video'
            ? 'You are a world-class director of photography and viral marketing strategist. Rewrite the given prompt into a powerful AI video generation brief. Add: cinematic camera movement (dolly, crane, handheld, whip-pan, etc.), professional lighting setup (golden hour, rim light, volumetric fog, neon glow, etc.), color grade and mood, and a visual hook that makes the viewer stop scrolling instantly. Make it feel like a Super Bowl ad or a luxury brand film campaign. Return ONLY the enhanced prompt — no explanation, no prefix, no quotes.'
            : 'You are a world-class commercial photographer and viral marketing expert with the mindset of Annie Leibovitz meets a top Nike creative director. Rewrite the given prompt into a powerful AI image generation brief. Add: camera lens and angle (85mm portrait, 24mm wide environmental, aerial, macro, etc.), professional lighting (Rembrandt, split light, rim light, golden hour, dramatic shadows, softbox, etc.), color palette and mood, compositional technique (rule of thirds, leading lines, negative space, symmetry), and the aspirational lifestyle feeling that makes marketing campaigns go viral. The result should feel like it belongs in Vogue, a Nike campaign, or an Apple product launch. Return ONLY the enhanced prompt — no explanation, no prefix, no quotes.';

        $response = Http::withToken(config('services.openai.key'))
            ->post(rtrim(config('services.openai.base_url', 'https://api.openai.com/v1'), '/') . '/chat/completions', [
                'model'       => 'gpt-4o-mini',
                'max_tokens'  => 500,
                'messages'    => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $validated['prompt']],
                ],
            ]);

        if ($response->failed()) {
            return response()->json(['message' => 'Failed to enhance prompt.'], 500);
        }

        $enhanced = trim($response->json('choices.0.message.content') ?? '');

        return response()->json(['enhanced' => $enhanced]);
    }
}
