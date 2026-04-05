<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GenerationAttribute extends Model
{
    protected $fillable = [
        'key', 'label', 'type', 'category', 'options',
        'default_value', 'min', 'max', 'step',
        'description', 'applicable_to', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
