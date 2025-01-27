<?php

namespace App\Models;

use App\Traits\Billable;
use App\Traits\Helpers;
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
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;

use function Illuminate\Events\queueable;

class User extends Authenticatable implements FilamentUser, HasAvatar //, MustVerifyEmail
{
    use Billable, HasFactory, Helpers, Notifiable, SoftDeletes, TwoFactorAuthenticatable;

    protected static function booted()
    {
        static::saved(queueable(function (self $user) {
            if ($user->wasRecentlyCreated || $user->wasChanged(['email', 'first_name', 'last_name', 'phone'])) {
                return $user->syncPaystackCustomerDetails();
            }
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

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
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
}
