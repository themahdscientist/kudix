<?php

namespace App;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasColor, HasIcon, HasLabel
{
    case Cash = 'cash';
    case Card = 'card';
    case WireTransfer = 'wire transfer';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Cash => 'success',
            self::Card => 'info',
            self::WireTransfer => 'info',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Cash => 'heroicon-o-minus-circle',
            self::Card => 'heroicon-o-check-circle',
            self::WireTransfer => 'heroicon-o-x-circle',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::Card => 'Card',
            self::WireTransfer => 'Wire transfer',
        };
    }
}
