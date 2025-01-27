<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ProductStatus: string implements HasColor, HasIcon, HasLabel
{
    case InStock = 'in-stock';
    case Discontinued = 'discontinued';
    case OutOfStock = 'out-of-stock';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::InStock => 'success',
            self::Discontinued => 'danger',
            self::OutOfStock => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::InStock => 'heroicon-o-check-circle',
            self::Discontinued => 'heroicon-o-x-circle',
            self::OutOfStock => 'heroicon-o-minus-circle',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::InStock => 'In stock',
            self::Discontinued => 'Discontinued',
            self::OutOfStock => 'Out of stock',
        };
    }
}
