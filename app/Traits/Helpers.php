<?php

namespace App\Traits;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

trait Helpers
{
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
            case 'cashier':
                return $this->role_id == Role::CASHIER;

            default:
                return false;
        }
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return Cache::remember("user-{$this->id}-avatar-url", now()->addHour(), function () {
            return $this->user_id
                ? Storage::url('logos/'.$this->query()->find($this->user_id)->load('setting')->setting->company_logo)
                : ($this->setting->company_logo ? Storage::url('logos/'.$this->setting->company_logo) : null);
        });
    }

    public function verified(): bool
    {
        if (isset($this->user_id)) {
            return $this->query()->find($this->user_id)->load('setting')->setting->kyc === 'verified';
        }

        return $this->setting->kyc === 'verified';
    }

    /**
     * Get the ISO 3166-1 alpha-2 code of the user's country
     */
    public function getLocation(): string
    {
        return $this->setting->iso3166_country_code;
    }
}
