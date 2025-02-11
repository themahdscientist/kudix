<?php

namespace App\Observers;

use App\Models;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Database\Eloquent\Model;

class InventoryObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Model $model): void
    {
        if (($model instanceof Models\ProductPurchase && $model->purchase->order_status === \App\Enums\OrderStatus::Received->value) || $model instanceof Models\ProductSale) {
            Models\Product::query()->find($model->product_id)->updateStockInVolume();
        }
    }

    public function updated(Model $model): void
    {
        if ($model instanceof Models\Purchase && $model->order_status === \App\Enums\OrderStatus::Received->value) {
            $model->products->each(function (Models\Product $product) {
                $product->updateStockInVolume();
            });
        }

        if ($model instanceof Models\ProductPurchase) {
            Models\Product::query()->find($model->product_id)->updateStockInDiscrete($model->id);
        }
    }
}
