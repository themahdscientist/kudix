<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

use function Illuminate\Events\queueable;

#[ObservedBy([\App\Observers\PharmacyObserver::class])]
#[ScopedBy([\App\Models\Scopes\PharmacyScope::class])]
class Document extends Model
{
    use HasFactory, SoftDeletes;

    public function newUniqueId(): string
    {
        return \App\Utils::generateDocumentId();
    }

    protected static function booted()
    {
        static::deleting(queueable(fn (Document $document) => $document->documentable()->delete()));
        static::restoring(queueable(fn (Document $document) => $document->documentable()->restore()));
        static::forceDeleting(queueable(fn (Document $document) => $document->documentable()->forceDelete()));
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
