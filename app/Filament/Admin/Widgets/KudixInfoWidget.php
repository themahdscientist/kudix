<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\Widget;

class KudixInfoWidget extends Widget
{
    protected static ?int $sort = -2;

    protected static bool $isLazy = false;

    protected static string $view = 'filament.admin.widgets.kudix-info-widget';
}
