<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([\App\Observers\PharmacyObserver::class])]
#[ScopedBy([\App\Models\Scopes\PharmacyScope::class])]
class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'loyalty_program_member' => 'boolean',
        ];
    }

    public function loyaltyProgram(): BelongsTo
    {
        return $this->belongsTo(LoyaltyProgram::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}
