<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum DocumentType: string implements HasColor, HasIcon, HasLabel
{
    case Invoice = 'invoice';
    case Receipt = 'receipt';

    public function getColor(): string|array|null
    {
        return 'gray';
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Invoice => 'heroicon-o-document-text',
            self::Receipt => 'heroicon-o-receipt-percent',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Invoice => 'Invoice',
            self::Receipt => 'Receipt',
        };
    }
}
