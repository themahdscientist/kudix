<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_admin' => 'boolean',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function salespeople(): HasMany
    {
        return $this->hasMany(Salesperson::class);
    }

    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        switch ($panel->getId()) {
            case 'admin':
                return $this->is_admin;
            default:
                return ! $this->is_admin;
        }
    }

    // ! Production code ty shii
    // public function canAccessPanel(Panel $panel): bool
    // {
    //     if ($panel->getId() === 'admin') {
    //         return $this->is_admin && $this->hasVerifiedEmail();
    //         return str_ends_with($this->email, '@yourdomain.com') && $this->hasVerifiedEmail();
    //     }

    //     return true;
    // }
}
