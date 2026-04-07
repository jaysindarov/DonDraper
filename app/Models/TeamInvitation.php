<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['team_id', 'email', 'token', 'expires_at'])]
class TeamInvitation extends Model
{
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
