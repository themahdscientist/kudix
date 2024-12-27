<?php

namespace App\Models;

use App\Traits\Billable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
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

use function Illuminate\Events\queueable;

class User extends Authenticatable implements FilamentUser, HasAvatar //, MustVerifyEmail
{
    use Billable, HasFactory, Notifiable, SoftDeletes, TwoFactorAuthenticatable;

    protected static function booted()
    {
        static::updated(queueable(function (self $user) {
            return $user->syncPaystackCustomerDetails();
        }));
    }

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
            'auth' => AsArrayObject::class,
            'email_verified_at' => 'datetime',
            'trial_ends_at' => 'datetime',
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
        return cache()->remember("user-{$this->id}-avatar-url", 60, function () {
            return $this->user_id
                ? Storage::url('logos/'.$this->query()->find($this->user_id)->setting->company_logo)
                : ($this->setting->company_logo ? Storage::url('logos/'.$this->setting->company_logo) : null);
        });
    }

    public function isOnBoarded(): bool
    {
        if (! ($this->setting->company_logo && $this->setting->bank_acc_no && $this->setting->bank_acc_name)) {
            return false;
        }

        return true;
    }
}
