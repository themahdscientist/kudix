<?php

namespace App\Filament\Admin\Pages;

use Filament\Actions\Action;
use Filament\Actions\StaticAction;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static string $routePath = 'dashboard';

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $activeNavigationIcon = 'heroicon-s-home';

    public $defaultAction = 'onboarding';

    public function onboarding(): Action
    {
        return Action::make('onboarding')
            ->modalIcon('heroicon-s-sparkles')
            ->modalHeading(
                new HtmlString("Welcome <span class='text-transparent bg-gradient-to-tr from-indigo-700 to-purple-700 dark:bg-gradient-to-r dark:from-indigo-500 dark:to-purple-500 bg-clip-text'>".Str::of(filament()->auth()->user()->name)->explode(' ')[0].'</span>')
            )
            ->modalDescription(
                'To ensure you get the most out of your subscription plan, consider updating your company\'s information.'
            )
            ->modalWidth(MaxWidth::Small)
            ->modalFooterActionsAlignment(Alignment::Center)
            ->modalCancelAction(false)
            ->modalSubmitAction(
                fn (StaticAction $action) => $action
                    ->label('Update')
                    ->icon('heroicon-s-arrow-path')
                    ->url(url('admin/profile'))
            )
            ->visible(fn (): bool => ! filament()->auth()->user()->isOnBoarded());
    }
}
