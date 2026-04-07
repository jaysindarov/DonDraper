<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'owner_id', 'credits', 'plan'])]
class Team extends Model
{
    protected function casts(): array
    {
        return [
            'credits' => 'integer',
        ];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'team_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function invitations()
    {
        return $this->hasMany(TeamInvitation::class);
    }

    public function generations()
    {
        return $this->hasMany(Generation::class);
    }

    public function deductCredits(int $amount): bool
    {
        if ($this->credits < $amount) {
            return false;
        }
        $this->decrement('credits', $amount);
        return true;
    }
}
