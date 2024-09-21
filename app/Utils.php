<?php

namespace App;

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
     * Generates the UUID for the invoice model.
     */
    public static function generateInvoiceId(): string
    {
        return env('APP_INVOICE_PREFIX').'-'.now()->format('ymd').'_'.Str::upper(Str::random(5));
    }

    /**
     * Generates the UUID for the document model.
     * Pass in `INV` to generate an Invoice ID or `RET` to generate a Receipt ID.
     *
     * @param  string  $type  The type of document to generate ID for. `INV|RET`.
     */
    // public static function generateDocumentId(string $type): string
    public static function generateDocumentId(): string
    {
        return env('APP_DOCUMENT_PREFIX').'-'.now()->format('ymd').'_'.Str::upper(Str::random(5));

        // if ($type === 'INV') {
        //     return env('APP_INVOICE_PREFIX').'-'.now()->format('ymd').'_'.Str::upper(Str::random(5));
        // } else {
        //     return env('APP_RECEIPT_PREFIX').'-'.now()->format('ymd').'_'.Str::upper(Str::random(5));
        // }
    }
}
