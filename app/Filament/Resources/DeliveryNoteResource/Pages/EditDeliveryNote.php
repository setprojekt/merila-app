<?php

namespace App\Filament\Resources\DeliveryNoteResource\Pages;

use App\Filament\Resources\DeliveryNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditDeliveryNote extends EditRecord
{
    protected static string $resource = DeliveryNoteResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);
        
        // Preusmeri na View stran, če je dobavnica arhivirana
        if ($this->record->archived) {
            $this->redirect(DeliveryNoteResource::getUrl('view', ['record' => $this->record]));
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\Action::make('print')
                ->label('Natisni PDF')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->action(function () {
                    // TODO: Implementiraj PDF generiranje z spatie/laravel-pdf
                    return redirect()->route('print.delivery-note', $this->record);
                })
                ->openUrlInNewTab(),
            Actions\DeleteAction::make()
                ->disabled(fn () => $this->record->archived),
        ];
    }
    
    protected function afterSave(): void
    {
        // Preveri, ali so vsa merila vrnjena in zaključi dobavnico
        $this->record->checkAndClose();
        
        if ($this->record->status === 'ZAKLJUCENA') {
            Notification::make()
                ->title('Dobavnica zaključena')
                ->body('Vsa merila so bila vrnjena in obdelana.')
                ->success()
                ->send();
        }
    }
}
