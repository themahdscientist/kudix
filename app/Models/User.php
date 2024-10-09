<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;

class User extends Authenticatable implements FilamentUser, HasAvatar//, MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
            'is_subscribed' => 'boolean',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function cashiers(): HasMany
    {
        return $this->hasMany(Cashier::class);
    }

    public function client(): HasOne
    {
        return $this->hasOne(Client::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function setting(): HasOne
    {
        return $this->hasOne(Setting::class);
    }

    public function isAdmin(): bool
    {
        return $this->role_id == Role::ADMIN;
    }

    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        switch ($panel->getId()) {
            case 'admin':
                if (! $this->hasVerifiedEmail()) {
                    return $this->isAdmin();
                }

                return $this->isAdmin() && $this->hasVerifiedEmail();
            case 'app':
                return ! $this->isAdmin();

            default:
                return false;
        }
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->setting->company_logo ? Storage::url('logos/'.$this->setting->company_logo) : null;
    }

    public function isOnBoarded(): bool
    {
        if (! $this->setting->company_logo) {
            return false;
        }

        return true;
    }
}
