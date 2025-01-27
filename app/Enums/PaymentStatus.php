<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatus: string implements HasColor, HasIcon, HasLabel
{
    case Overdue = 'overdue';
    case Paid = 'paid';
    case Pending = 'pending';
    case Refunded = 'refunded';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Overdue => 'danger',
            self::Paid => 'success',
            self::Pending => 'warning',
            self::Refunded => 'info',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Overdue => 'heroicon-o-x-circle',
            self::Paid => 'heroicon-o-check-circle',
            self::Pending => 'heroicon-o-minus-circle',
            self::Refunded => 'heroicon-o-arrow-uturn-left',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Overdue => 'Overdue',
            self::Paid => 'Paid',
            self::Pending => 'Pending',
            self::Refunded => 'Refunded',
        };
    }
}
