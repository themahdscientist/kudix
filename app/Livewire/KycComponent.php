<?php

namespace App\Livewire;

use Binkode\Paystack\Support\Miscellaneous;
use Binkode\Paystack\Support\Verification;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Jeffgreco13\FilamentBreezy\Livewire\MyProfileComponent;

class KycComponent extends MyProfileComponent
{
    protected $listeners = ['refresh' => '$refresh'];

    protected string $view = 'livewire.kyc-component';

    public array $only = [
        'account_name',
        'account_number',
        'bank_code',
    ];

    public array $data;

    public $setting;

    public $user;

    public static $sort = 22;

    public function mount()
    {
        $this->user = filament()->auth()->user()->load('setting');

        $this->setting = $this->user->setting;

        $this->form->fill($this->setting->only($this->only));
    }

    public function form(Form $form): Form
    {
        return $form
            ->disabled(fn () => $this->user->setting->kyc === 'verified')
            ->schema([
                Components\TextInput::make('account_number')
                    ->helperText("{$this->setting->account_name}")
                    ->required(),
                Components\Select::make('bank_code')
                    ->label('Bank name')
                    ->options(function () {
                        $options = [
                            'country' => 'nigeria',
                            'currency' => 'NGN',
                        ];

                        if (is_null(Cache::get('banks'))) {
                            Cache::put('banks', rescue(fn () => Miscellaneous::listBanks($options), ['status' => false]), now()->addMonths(3));
                            $res = Cache::get('banks');
                        } elseif (! Cache::get('banks')['status']) {
                            Cache::forget('banks');
                            Cache::put('banks', rescue(fn () => Miscellaneous::listBanks($options), ['status' => false]), now()->addMonths(3));
                            $res = Cache::get('banks');
                        } else {
                            $res = Cache::get('banks');
                        }

                        if ($res['status']) {
                            return collect($res['data'])->pluck('name', 'code')->toArray();
                        }

                        return [];
                    })
                    ->searchable()
                    ->required(),
                Components\Livewire::make(KycStatus::class)
                    ->lazy(),
            ])
            ->statePath('data');
    }

    public function verify(): void
    {
        if ($this->user->verified()) {
            Notification::make('info')
                ->title('KYC verified')
                ->info()
                ->persistent()
                ->send();

            return;
        }

        $form = collect($this->form->getState())->only($this->only)->all();

        // resolve account - standard kyc
        $options = [
            'account_number' => $form['account_number'],
            'bank_code' => $form['bank_code'],
        ];

        $res = rescue(fn () => Verification::resolve($options), ['status' => false]);

        if ($res['status']) {
            $data = collect($res['data'])->only(['account_number', 'account_name'])->all();

            if (str_contains(strtolower($data['account_name']), strtolower($this->user->paystackFirstName())) && str_contains(strtolower($data['account_name']), strtolower($this->user->paystackLastName()))) {
                $this->setting->update([
                    'account_name' => $data['account_name'],
                    'account_number' => $data['account_number'],
                    'bank_code' => $form['bank_code'],
                    'kyc' => 'verified',
                ]);

                $this->dispatch('refresh');

                Notification::make('info')
                    ->title('KYC verified successfully')
                    ->success()
                    ->persistent()
                    ->send();

                return;
            }

            Notification::make('info')
                ->title('Mismatch')
                ->body('Account name does not match the name on your profile.')
                ->danger()
                ->persistent()
                ->send();
        } else {
            Notification::make('info')
                ->title('Offline')
                ->body('Seems like you are offline, please check your internet connection and try again')
                ->warning()
                ->send();
        }
    }
}
