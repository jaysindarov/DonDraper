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
    ];

    protected function casts(): array
    {
        return [
            'attributes' => 'array',
            'metadata' => 'array',
            'reference_persons' => 'array',
        ];
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
