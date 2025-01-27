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
#[ObservedBy(\App\Observers\SaleObserver::class)]
#[ScopedBy([\App\Models\Scopes\PharmacyScope::class])]
class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted()
    {
        static::deleting(queueable(fn (Sale $sale) => $sale->document()->delete()));
        static::restoring(queueable(fn (Sale $sale) => $sale->document()->restore()));
        static::forceDeleting(queueable(fn (Sale $sale) => $sale->document()->forceDelete()));
    }

    public function newUniqueId(): string
    {
        return \App\Utils::generateSaleId();
    }

    public function casts(): array
    {
        return [
            'discount' => Casts\PercentCast::class,
            'shipping' => Casts\MoneyCast::class,
            'tendered' => Casts\MoneyCast::class,
            'total_price' => Casts\MoneyCast::class,
            'vat' => Casts\PercentCast::class,
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function document(): MorphOne
    {
        return $this->morphOne(Document::class, 'documentable');
    }

    public function productSales(): HasMany
    {
        return $this->hasMany(ProductSale::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withPivot(['quantity', 'unit_price']);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}
