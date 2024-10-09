<?php

namespace App\Models;

use App\Casts;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

use function Illuminate\Events\queueable;

#[ObservedBy([\App\Observers\PharmacyObserver::class])]
#[ObservedBy([\App\Observers\InventoryObserver::class])]
#[ScopedBy([\App\Models\Scopes\PharmacyScope::class])]
class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted()
    {
        static::deleting(queueable(fn (Purchase $purchase) => $purchase->document()->delete()));
        static::restoring(queueable(fn (Purchase $purchase) => $purchase->document()->restore()));
        static::forceDeleting(queueable(fn (Purchase $purchase) => $purchase->document()->forceDelete()));
    }

    public function newUniqueId(): string
    {
        return \App\Utils::generatePurchaseId();
    }

    public function casts(): array
    {
        return [
            'expected_delivery_date' => 'datetime',
            'discount' => Casts\PercentCast::class,
            'received_date' => 'datetime',
            'shipping' => Casts\MoneyCast::class,
            'tendered' => Casts\MoneyCast::class,
            'total_cost' => Casts\MoneyCast::class,
            'vat' => Casts\PercentCast::class,
        ];
    }

    public function document(): MorphOne
    {
        return $this->morphOne(Document::class, 'documentable');
    }

    public function productPurchases(): HasMany
    {
        return $this->hasMany(ProductPurchase::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withPivot(['quantity', 'unit_cost']);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);

    }
}
