<?php

namespace App\Livewire;

use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Jeffgreco13\FilamentBreezy\Livewire\MyProfileComponent;

class BusinessInfoComponent extends MyProfileComponent
{
    protected string $view = 'livewire.business-info-component';

    public array $only = [
        'company_name',
        'company_address',
        'company_about',
        'company_logo',
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
                Components\FileUpload::make('company_logo')
                    ->label('Logo')
                    ->hintColor('warning')
                    ->hintIcon('heroicon-s-exclamation-circle', '10MB max file size.')
                    ->disk('logos')
                    ->avatar()
                    ->image()
                    ->imageResizeMode('contain')
                    ->imageResizeUpscale(false)
                    ->imageCropAspectRatio('1:1')
                    ->imageResizeTargetWidth('100')
                    ->imageResizeTargetHeight('100')
                    ->maxSize(10240)
                    ->uploadingMessage('Formatting logo...')
                    ->alignCenter()
                    ->required(),
                Components\TextInput::make('company_name')
                    ->label('Name')
                    ->placeholder('JavaTechnovation Holdings.')
                    ->required()
                    ->maxLength(255),
                Components\TextInput::make('company_address')
                    ->label('Address')
                    ->placeholder('street/suburb, city, state')
                    ->required()
                    ->maxLength(255),
                Components\Textarea::make('company_about')
                    ->label('About')
                    ->placeholder('Your content goes in here...')
                    ->rows(1)
                    ->columnSpanFull(),
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
