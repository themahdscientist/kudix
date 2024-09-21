<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([\App\Observers\PharmacyObserver::class])]
#[ScopedBy([\App\Models\Scopes\PharmacyScope::class])]
class Document extends Model
{
    use HasFactory, SoftDeletes;

    public function newUniqueId(): string
    {
        return \App\Utils::generateDocumentId();
    }

    public function casts(): array
    {
        return [
            'amount' => MoneyCast::class,
            'amount_paid' => MoneyCast::class,
            'due_date' => 'datetime',
            'payment_date' => 'datetime',
        ];
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }
}
