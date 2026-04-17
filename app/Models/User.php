<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'credits', 'avatar', 'plan', 'is_admin', 'current_team_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, Billable;

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

    public function creditTransactions()
    {
        return $this->hasMany(CreditTransaction::class);
    }

    public function deductCredits(int $amount = 1, ?int $generationId = null, ?string $description = null): bool
    {
        if ($this->credits < $amount) {
            return false;
        }
        $this->decrement('credits', $amount);

        CreditTransaction::create([
            'user_id'       => $this->id,
            'team_id'       => $this->current_team_id,
            'generation_id' => $generationId,
            'type'          => 'debit',
            'amount'        => -$amount,
            'balance_after' => $this->fresh()->credits,
            'description'   => $description ?? "Generation credit deduction",
        ]);

        return true;
    }

    public function addCredits(int $amount, string $type = 'topup', ?string $description = null): void
    {
        $this->increment('credits', $amount);

        CreditTransaction::create([
            'user_id'       => $this->id,
            'team_id'       => $this->current_team_id,
            'type'          => $type,
            'amount'        => $amount,
            'balance_after' => $this->fresh()->credits,
            'description'   => $description ?? "Credits added ({$type})",
        ]);
    }
}
