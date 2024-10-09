<?php

namespace App\Forms\Components;

use Filament\Forms;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class CashierField extends Forms\Components\Field
{
    public static function getComponent($sales = false): array
    {
        $base = [];

        return $base;
    }
}
