<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Nadzorna plošča';
    
    protected static ?string $title = 'Nadzorna plošča';
    
    protected static ?string $navigationIcon = 'heroicon-o-home';
}
