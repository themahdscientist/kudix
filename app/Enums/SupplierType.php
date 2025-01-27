<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum SupplierType: string implements HasColor, HasIcon, HasLabel
{
    case Manufacturer = 'manufacturer';
    case Distributor = 'distributor';
    case Wholesaler = 'wholesaler';
    case Importer = 'importer';
    case DropShipper = 'drop-shipper';
    case GovernmentAgency = 'government-agency';
    case NonProfitOrganization = 'non-profit-organization';
    case Individual = 'individual';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Manufacturer => 'success',
            self::Distributor => 'danger',
            self::Wholesaler => 'danger',
            self::Importer => 'success',
            self::DropShipper => 'info',
            self::GovernmentAgency => 'warning',
            self::NonProfitOrganization => 'warning',
            self::Individual => 'info',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Manufacturer => 'heroicon-o-home-modern',
            self::Distributor => 'heroicon-o-truck',
            self::Wholesaler => 'heroicon-o-gift',
            self::Importer => 'heroicon-o-globe-alt',
            self::DropShipper => 'heroicon-o-rocket-launch',
            self::GovernmentAgency => 'heroicon-o-building-library',
            self::NonProfitOrganization => 'heroicon-o-building-office',
            self::Individual => 'heroicon-o-user',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Manufacturer => 'Manufacturer',
            self::Distributor => 'Distributor',
            self::Wholesaler => 'Wholesaler',
            self::Importer => 'Importer',
            self::DropShipper => 'Drop shipper',
            self::GovernmentAgency => 'Government agency',
            self::NonProfitOrganization => 'Non-profit organization',
            self::Individual => 'Individual',
        };
    }
}
