<?php

namespace App;

use App\Models\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

abstract class Utils
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public static function responsive(): array|int
    {
        if (in_array('create', request()->segments()) || in_array('update', request()->segments())) {
            return ['sm' => 2, 'md' => 4];
        }

        return 3;
    }

    /**
     * Generates the UUID for the sale model.
     */
    public static function generateSaleId(): string
    {
        return env('APP_SALE_PREFIX').'-'.now()->format('ymd').'_'.Str::upper(Str::random(5));
    }

    /**
     * Generates the UUID for the purchase model.
     */
    public static function generatePurchaseId(): string
    {
        return env('APP_PURCHASE_PREFIX').'-'.now()->format('ymd').'_'.Str::upper(Str::random(5));
    }

    /**
     * Generates the UUID for the document model.
     */
    public static function generateDocumentId(): string
    {
        return env('APP_DOCUMENT_PREFIX').'-'.now()->format('ymd').'_'.Str::upper(Str::random(5));
    }

    public static function furnishUser(Model $record, int $role, ?array $data = [])
    {
        $record->country = filament()->auth()->user()->country;

        if ($record instanceof Client) {
            $record->clientInfo()->create($data);
        }

        $record->role()->associate($role)->save();
    }
}
