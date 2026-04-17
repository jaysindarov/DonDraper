<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreGenerationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $validImageModels = array_keys(array_diff_key(config('ai_models'), ['video_models' => true]));
        $validVideoModels = array_keys(config('ai_models.video_models'));
        $allModels = array_merge($validImageModels, $validVideoModels);

        return [
            'type'            => 'required|in:image,video',
            'prompt'          => 'required|string|max:2000',
            'negative_prompt' => 'nullable|string|max:1000',
            'model'           => ['nullable', 'string', 'in:' . implode(',', $allModels)],
            'attributes'      => 'nullable|array',
            'product_type'    => 'nullable|string|max:100',
        ];
    }
}
