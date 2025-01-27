<?php

namespace App\Filament\Cashier\Pages;

use Filament\Actions\Action;
use Filament\Actions\StaticAction;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\MaxWidth;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static string $routePath = 'dashboard';

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $activeNavigationIcon = 'heroicon-s-home';

    public $defaultAction = 'onboarding';

    public function onboarding(): Action
    {
        return Action::make('onboarding')
            ->modalIcon('heroicon-s-check-badge')
            ->modalHeading('Complete KYC')
            ->modalDescription(
                'Before you get started, please complete the KYC process to verify your account.'
            )
            ->modalWidth(MaxWidth::Small)
            ->modalFooterActionsAlignment(Alignment::Center)
            ->modalCancelAction(false)
            ->modalSubmitAction(
                fn (StaticAction $action) => $action
                    ->label('Verify')
                    ->icon('heroicon-s-check-badge')
                    ->url(url('admin/profile'))
            )
            ->visible(fn (): bool => ! filament()->auth()->user()->verified());
    }
}
