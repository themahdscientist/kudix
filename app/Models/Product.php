<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([\App\Observers\PharmacyObserver::class])]
#[ScopedBy([\App\Models\Scopes\PharmacyScope::class])]
class Product extends Model
{
    use HasFactory, SoftDeletes;

    public function casts(): array
    {
        return [
            'price' => MoneyCast::class,
        ];
    }

    public function productPurchases(): HasMany
    {
        return $this->hasMany(ProductPurchase::class);
    }

    public function productSales(): HasMany
    {
        return $this->hasMany(ProductSale::class);
    }

    public function purchases(): BelongsToMany
    {
        return $this->belongsToMany(Purchase::class)->withPivot(['quantity', 'unit_price']);
    }

    public function sales(): BelongsToMany
    {
        return $this->belongsToMany(Sale::class)->withPivot(['quantity', 'unit_cost']);
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class);
    }

    public function updateStock(): void
    {
        $this->quantity = $this->productPurchases()->sum('quantity') - $this->productSales()->sum('quantity');

        if ($this->quantity > 0) {
            $this->status = \App\ProductStatus::InStock->value;
        } else {
            $this->status = \App\ProductStatus::OutOfStock->value;
        }

        $this->saveQuietly();
    }
}
