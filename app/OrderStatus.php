<?php

namespace App;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasColor, HasIcon, HasLabel
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Shipped = 'shipped';
    case Received = 'received';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'info',
            self::Shipped => 'danger',
            self::Received => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Pending => 'heroicon-o-minus-circle',
            self::Approved => 'heroicon-o-check-circle',
            self::Shipped => 'heroicon-o-truck',
            self::Received => 'heroicon-o-gift',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Shipped => 'Shipped',
            self::Received => 'Received',
        };
    }
}
