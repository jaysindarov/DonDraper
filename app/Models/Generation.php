<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Generation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
        'status',
        'prompt',
        'negative_prompt',
        'product_type',
        'product_image_path',
        'product_image_paths',
        'reference_persons',
        'model',
        'provider',
        'attributes',
        'result_url',
        'thumbnail_url',
        'width',
        'height',
        'steps',
        'guidance_scale',
        'seed',
        'credits_used',
        'error_message',
        'metadata',
        'is_public',
        'team_id',
    ];

    protected function casts(): array
    {
        return [
            'attributes'           => 'array',
            'metadata'             => 'array',
            'reference_persons'    => 'array',
            'product_image_paths'  => 'array',
            'is_public'            => 'boolean',
        ];
    }

    /**
     * Returns all product image paths regardless of whether they were stored
     * as a multi-image array (new) or a single path string (legacy).
     *
     * @return string[]
     */
    public function allProductImagePaths(): array
    {
        if (!empty($this->product_image_paths)) {
            return $this->product_image_paths;
        }

        if ($this->product_image_path) {
            return [$this->product_image_path];
        }

        return [];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
