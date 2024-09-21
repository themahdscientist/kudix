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

#[ObservedBy([\App\Observers\PharmacyObserver::class])]
#[ObservedBy(\App\Observers\SaleObserver::class)]
#[ScopedBy([\App\Models\Scopes\PharmacyScope::class])]
class Sale extends Model
{
    use HasFactory, SoftDeletes;

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
            'total_cost' => Casts\MoneyCast::class,
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

    public function invoice(): MorphOne
    {
        return $this->morphOne(Invoice::class, 'invoiceable');
    }

    public function productSales(): HasMany
    {
        return $this->hasMany(ProductSale::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withPivot(['quantity', 'unit_cost']);
    }

    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesperson_id');
    }
}
