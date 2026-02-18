<?php

namespace App\Filament\Resources\DeliveryNoteResource\Pages;

use App\Filament\Resources\DeliveryNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDeliveryNote extends ViewRecord
{
    protected static string $resource = DeliveryNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->disabled(fn () => $this->record->archived),
        ];
    }
}
