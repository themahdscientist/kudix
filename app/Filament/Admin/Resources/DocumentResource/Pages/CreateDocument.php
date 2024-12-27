<?php

namespace App\Filament\Admin\Resources\DocumentResource\Pages;

use App\DocumentableType;
use App\Filament\Admin\Resources\DocumentResource;
use App\Filament\Resources\Pages\CreateRecord;
use App\Models\Purchase;
use App\Models\Sale;
use Filament\Actions\Action;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\HtmlString;

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    public $defaultAction = 'onboarding';

    public function onboarding(): Action
    {
        return Action::make('onboarding')
            ->modalIcon('heroicon-s-exclamation-triangle')
            ->modalIconColor('danger')
            ->modalHeading('User Intervention')
            ->modalDescription(new HtmlString('<b>When changing the values for your documentable search<br/>ALWAYS CLEAR THE PENDING SEARCH FIRST BEFORE RESEARCHING.</b>'))
            ->modalWidth(MaxWidth::Small)
            ->modalFooterActionsAlignment(Alignment::Center)
            ->modalCancelAction(false)
            ->modalSubmitActionLabel('Got it!')
            ->visible(true);
    }
}
