<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class InstrumentsGuide extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    
    protected static string $view = 'filament.pages.instruments-guide';
    
    protected static ?string $title = 'Navodila za uporabo modula Merila';
    
    protected static ?string $navigationLabel = 'Navodila';
    
    protected static ?string $navigationGroup = 'Pomoč';
    
    protected static ?int $navigationSort = 9999;
}
