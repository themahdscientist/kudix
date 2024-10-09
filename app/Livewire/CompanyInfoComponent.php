<?php

namespace App\Livewire;

use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Jeffgreco13\FilamentBreezy\Livewire\MyProfileComponent;

class CompanyInfoComponent extends MyProfileComponent
{
    protected string $view = 'livewire.company-info-component';

    public array $only = [
        'bank_acc_name',
        'bank_acc_no',
        'company_name',
        'company_address',
        'company_about',
        'company_logo',
        'discount',
        'vat',
    ];

    public array $data;

    public $user;

    public $userClass;

    public static $sort = 21;

    public function mount()
    {
        $this->user = filament()->auth()->user()->load('setting');
        $this->userClass = get_class($this->user);

        $this->form->fill($this->user->setting->only($this->only));
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Grid::make(4)
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
                        Components\Section::make([
                            Components\Grid::make()
                                ->schema([
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
                                ]),
                            Components\Textarea::make('company_about')
                                ->label('About')
                                ->placeholder('Your content goes in here...')
                                ->rows(1)
                                ->columnSpanFull(),
                        ])
                            ->columnSpan(3),
                        Components\Section::make('Online Sales Information')
                            ->schema([
                                Components\Group::make([
                                    Components\TextInput::make('discount')
                                        ->numeric()
                                        ->suffix('%')
                                        ->default(0)
                                        ->minValue(0)
                                        ->maxValue(42949672.95),
                                    Components\TextInput::make('vat')
                                        ->label('VAT')
                                        ->numeric()
                                        ->suffix('%')
                                        ->default(5)
                                        ->minValue(0)
                                        ->maxValue(42949672.95),
                                ])
                                    ->columnSpan(1),
                                Components\Group::make([
                                    Components\TextInput::make('bank_acc_name')
                                        ->label('Bank account name'),
                                    Components\TextInput::make('bank_acc_no')
                                        ->label('Bank account number'),
                                ])
                                    ->columnSpan(3),
                            ])
                            ->columns(4),
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = collect($this->form->getState())->only($this->only)->all();
        $this->user->setting->update($data);
        Notification::make('success')
            ->success()
            ->title(__('Company Information updated successfully'))
            ->send();
    }
}
