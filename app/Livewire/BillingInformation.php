<?php

namespace App\Livewire;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

#[Lazy]
#[Layout('components.layouts.billing')]
#[Title('Update billing details')]
class BillingInformation extends Component implements HasForms
{
    use InteractsWithForms;
    use WithRateLimiting;

    public ?array $data = [];

    public $user;

    public function mount(): void
    {
        $this->user = filament()->auth()->user()->load('setting');
        $this->form->fill($this->user->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->required(),
                                PhoneInput::make('phone')
                                    ->label('Phone number')
                                    ->prefixIcon('heroicon-s-phone')
                                    ->defaultCountry('NG')
                                    ->onlyCountries([$this->user->country])
                                    ->autoPlaceholder('aggressive')
                                    ->strictMode()
                                    ->required(),
                            ]),
                        Forms\Components\Fieldset::make('setting')
                            ->label('Business')
                            ->schema([
                                Forms\Components\TextInput::make('setting.company_name')
                                    ->label('Name')
                                    ->required(),
                                Forms\Components\TextInput::make('setting.company_address')
                                    ->label('Address')
                                    ->required(),
                            ]),
                    ]),
            ])
            ->model($this->user)
            ->statePath('data');
    }

    public function save()
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            \App\Utils::getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = collect($this->form->getState());

        $this->user->update($data->only(['email', 'phone'])->toArray());

        $this->user->setting->update($data->only(['setting'])->toArray()['setting']);

        Notification::make('success')
            ->title('Success')
            ->body('Billing information updated.')
            ->success()
            ->send();

        return $this->redirectRoute('billing.index', navigate: true);
    }

    public function cancel()
    {
        return $this->redirectRoute('billing.index', navigate: true);
    }
}
