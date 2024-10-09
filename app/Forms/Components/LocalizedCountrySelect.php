<?php

namespace App\Forms\Components;

use Filament\Forms\Components;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use League\ISO3166\ISO3166;

class LocalizedCountrySelect extends Components\Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $iso3166 = new ISO3166;

        foreach ($iso3166 as $data) {
            $this->options[$data['alpha2']] = locale_get_display_region("-{$data['alpha2']}", app()->currentLocale());
        }

        $this->searchable();
        $this->suffixAction(
            Components\Actions\Action::make('location')
                ->icon('heroicon-s-map-pin')
                ->action(function (Components\Select $component) {
                    $component->state(rescue(
                        fn () => Http::get('https://ipinfo.io', ['token' => env('IPINFO_SECRET')])->json('country'),
                        function () {
                            Notification::make('error')
                                ->icon('heroicon-s-signal-slash')
                                ->title('Offline')
                                ->body('You\'ve lost internet connectivity.')
                                ->warning()
                                ->send();

                            return 'NG';
                        },
                        false
                    ));
                })
        );
        $this->required();
    }
}
