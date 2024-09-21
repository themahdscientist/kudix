<?php

namespace App\Providers;

use Filament\Tables;
use Filament\Tables\Actions;
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
        \Illuminate\Support\Facades\DB::connection()->setQueryGrammar(new \App\Database\Query\Grammars\MariaDBGrammar);

        \Illuminate\Database\Eloquent\Model::unguard();

        \Illuminate\Database\Eloquent\Relations\Relation::enforceMorphMap([
            'purchase' => 'App\Models\Purchase',
            'sale' => 'App\Models\Sale',
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
    }
}
