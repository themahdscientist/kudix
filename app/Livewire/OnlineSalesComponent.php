<?php

namespace App\Livewire;

use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Jeffgreco13\FilamentBreezy\Livewire\MyProfileComponent;

class OnlineSalesComponent extends MyProfileComponent
{
    protected string $view = 'livewire.online-sales-component';

    public array $only = [
        'vat',
        'discount',
    ];

    public array $data;

    public $setting;

    public static $sort = 21;

    public function mount()
    {
        $this->setting = filament()->auth()->user()->load('setting')->setting;

        $this->form->fill($this->setting->only($this->only));
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\TextInput::make('vat')
                    ->label('VAT')
                    ->integer()
                    ->suffix('%')
                    ->minValue(0)
                    ->maxValue(20)
                    ->required(),
                Components\TextInput::make('discount')
                    ->integer()
                    ->suffix('%')
                    ->minValue(0)
                    ->required(),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = collect($this->form->getState())->only($this->only)->all();
        $this->setting->update($data);
        Notification::make('success')
            ->success()
            ->title(__('Company Information updated successfully'))
            ->send();
    }
}
