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
            ? 'You are an expert at writing AI video generation prompts. Enhance the given prompt to be more descriptive, cinematic, and detailed. Include camera movement, lighting, mood, and visual style. Return ONLY the enhanced prompt — no explanation, no prefix, no quotes.'
            : 'You are an expert at writing AI image generation prompts. Enhance the given prompt to be more descriptive, detailed, and vivid. Include lighting, composition, style, and atmosphere. Return ONLY the enhanced prompt — no explanation, no prefix, no quotes.';

        $response = Http::withToken(config('services.openai.key'))
            ->post('https://api.openai.com/v1/chat/completions', [
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
