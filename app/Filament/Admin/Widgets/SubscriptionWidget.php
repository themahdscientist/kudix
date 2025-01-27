<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\Widget;

class SubscriptionWidget extends Widget
{
    protected static ?int $sort = -3;

    protected static bool $isLazy = false;

    protected static string $view = 'filament.admin.widgets.subscription-widget';
}
