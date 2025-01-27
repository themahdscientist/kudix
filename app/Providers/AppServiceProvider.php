<?php

namespace App\Providers;

use Filament\Forms;
use Filament\Support\Facades\FilamentView;
use Filament\Tables;
use Filament\Tables\Actions;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::STYLES_AFTER,
            fn (): ?View => filament()->auth()->check() ?
            view('render-hook-styles') :
            null
        );

        // go back home button
        // FilamentView::registerRenderHook(
        //     PanelsRenderHook::SIMPLE_PAGE_START,
        //     fn (): ?string => ! filament()->auth()->check() ?
        //     Blade::render('@svg("") <a href="/">Home</a>') :
        //     null,
        // );

        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_START,
            fn (): ?View => filament()->auth()->check() ?
            view('livewire.render-hooks.trial-countdown') :
            null,
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_START,
            fn (): ?View => filament()->auth()->check() ?
            view('livewire.render-hooks.complete-kyc') :
            null,
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::SCRIPTS_AFTER,
            fn (): ?View => filament()->auth()->check() ?
            view('render-hook-scripts') :
            null
        );

        \Illuminate\Support\Facades\DB::connection()->setQueryGrammar(new \App\Database\Query\Grammars\MariaDBGrammar);

        \Illuminate\Database\Eloquent\Model::unguard();

        \Illuminate\Database\Eloquent\Relations\Relation::enforceMorphMap([
            'purchase' => 'App\Models\Purchase',
            'sale' => 'App\Models\Sale',
            'user' => 'App\Models\User',
        ]);

        Actions\Action::configureUsing(function (Actions\Action $action): void {
            $action
                ->modalCloseButton(false)
                ->iconSize(\Filament\Support\Enums\IconSize::Small);
        });

        Actions\ViewAction::configureUsing(function (Actions\ViewAction $action): void {
            $action
                ->iconButton();
        });

        Actions\EditAction::configureUsing(function (Actions\EditAction $action): void {
            $action
                ->iconButton();
        });

        Actions\DeleteAction::configureUsing(function (Actions\DeleteAction $action): void {
            $action
                ->iconButton();
        });

        Actions\ForceDeleteAction::configureUsing(function (Actions\ForceDeleteAction $action): void {
            $action
                ->iconButton();
        });

        Actions\RestoreAction::configureUsing(function (Actions\RestoreAction $action): void {
            $action
                ->iconButton();
        });

        Actions\DetachAction::configureUsing(function (Actions\DetachAction $action): void {
            $action
                ->iconButton();
        });

        Actions\DissociateAction::configureUsing(function (Actions\DissociateAction $action): void {
            $action
                ->iconButton();
        });

        Tables\Columns\Column::configureUsing(function (Tables\Columns\Column $column): void {
            $column
                ->alignCenter();
        });

        Tables\Table::configureUsing(function (Tables\Table $table): void {
            $table
                ->actionsPosition(Tables\Enums\ActionsPosition::BeforeColumns)
                ->striped();
        });

        Tables\Columns\Summarizers\Summarizer::configureUsing(function (Tables\Columns\Summarizers\Summarizer $summarizer): void {
            $summarizer
                ->label('');
        });

        Forms\Components\Field::configureUsing(function (Forms\Components\Field $field): void {
            if ($field instanceof Forms\Components\TextInput || $field instanceof Forms\Components\Textarea) {
                $field
                    ->dehydrated(fn (mixed $state) => filled($state))
                    ->dehydrateStateUsing(fn (mixed $state) => \Illuminate\Support\Str::squish($state));
            }
        });

    }
}
