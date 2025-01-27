<?php

namespace App;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

use function Livewire\Volt\title;

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

    public static function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        return Notification::make('rate_limit')
            ->title('Too many failed requests')
            ->body(array_key_exists('body', __('filament-panels::pages/auth/login.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/login.notifications.throttled.body', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => $exception->minutesUntilAvailable,
            ]) : null)
            ->danger();
    }
}
