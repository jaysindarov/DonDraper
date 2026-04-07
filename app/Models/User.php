<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'credits', 'avatar', 'plan', 'is_admin', 'current_team_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_admin'          => 'boolean',
        ];
    }

    public function generations()
    {
        return $this->hasMany(Generation::class);
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function ownedTeams()
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    public function currentTeam()
    {
        return $this->belongsTo(Team::class, 'current_team_id');
    }

    public function deductCredits(int $amount = 1): bool
    {
        if ($this->credits < $amount) {
            return false;
        }
        $this->decrement('credits', $amount);
        return true;
    }
}
