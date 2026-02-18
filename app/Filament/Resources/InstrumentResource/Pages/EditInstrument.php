<?php

namespace App\Filament\Resources\InstrumentResource\Pages;

use App\Filament\Resources\InstrumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInstrument extends EditRecord
{
    protected static string $resource = InstrumentResource::class;

    protected static ?string $title = 'Uredi Merilo';

    protected static ?string $navigationLabel = 'Uredi Merilo';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Izbriši'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return InstrumentResource::getUrl('index');
    }
}
