<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default AI Provider Names
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the AI providers below should be the
    | default for AI operations when no explicit provider is provided
    | for the operation. This should be any provider defined below.
    |
    */

    'default'                   => 'openai',
    'default_for_images'        => 'openai',
    'default_for_audio'         => 'openai',
    'default_for_transcription' => 'openai',
    'default_for_embeddings'    => 'openai',
    'default_for_reranking'     => 'cohere',

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    */

    'caching' => [
        'embeddings' => [
            'cache' => false,
            'store' => env('CACHE_STORE', 'database'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Providers
    |--------------------------------------------------------------------------
    |
    | NOTE: env var names deliberately match config/services.php so a single
    | .env entry covers both the custom providers and the Laravel AI SDK.
    |
    |   OPENAI_API_KEY   → openai  (shared with services.openai.key)
    |   GOOGLE_AI_API_KEY → gemini  (shared with services.google.api_key)
    |   GROK_API_KEY     → xai     (shared with services.grok.key)
    |
    */

    'providers' => [
        'openai' => [
            'driver' => 'openai',
            'key'    => env('OPENAI_API_KEY'),
            'url'    => env('OPENAI_URL', 'https://api.openai.com/v1'),
        ],

        'gemini' => [
            'driver' => 'gemini',
            'key'    => env('GOOGLE_AI_API_KEY'),
        ],

        'xai' => [
            'driver' => 'xai',
            'key'    => env('GROK_API_KEY'),
        ],

        'anthropic' => [
            'driver' => 'anthropic',
            'key'    => env('ANTHROPIC_API_KEY'),
        ],

        'cohere' => [
            'driver' => 'cohere',
            'key'    => env('COHERE_API_KEY'),
        ],

        'deepseek' => [
            'driver' => 'deepseek',
            'key'    => env('DEEPSEEK_API_KEY'),
        ],

        'eleven' => [
            'driver' => 'eleven',
            'key'    => env('ELEVENLABS_API_KEY'),
        ],

        'groq' => [
            'driver' => 'groq',
            'key'    => env('GROQ_API_KEY'),
        ],

        'mistral' => [
            'driver' => 'mistral',
            'key'    => env('MISTRAL_API_KEY'),
        ],

        'ollama' => [
            'driver' => 'ollama',
            'key'    => env('OLLAMA_API_KEY', ''),
            'url'    => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
        ],

    ],

];
