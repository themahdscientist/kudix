<?php

namespace App\Filament\Website\Pages;

use Filament\Pages\Page;

class Index extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.website.pages.index';
}
