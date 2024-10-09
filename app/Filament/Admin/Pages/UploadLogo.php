<?php

namespace App\Filament\Admin\Pages;

use Filament\Forms\Form;
use Filament\Pages\SimplePage;

class UploadLogo extends SimplePage
{
    /**
     * @var view-string
     */
    protected static string $view = 'filament.admin.pages.upload-logo';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function form(Form $form): Form
    {
        return $form;
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }
}
