<?php

namespace App\Filament\Pages;

use App\Filament\Resources\InstrumentResource;
use App\Models\Instrument;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Carbon\Carbon;

class ArchivedInstruments extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    
    protected static ?string $navigationLabel = 'Arhivirana merila';
    
    protected static ?string $title = 'Arhivirana merila';
    
    protected static ?string $navigationGroup = 'Merila';
    
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.archived-instruments';
    
    protected function getTableQuery(): Builder
    {
        return Instrument::query()
            ->withTrashed()
            ->where('archived', true);
    }
    
    public function table(Table $table): Table
    {
        // Uporabi tabelo iz InstrumentResource, vendar preskoči modifyQueryUsing z where('archived', false)
        // Dodaj naš query za arhivirana merila
        return InstrumentResource::table($table, skipArchivedFilter: true)
            ->query($this->getTableQuery());
    }
}
