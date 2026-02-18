<?php

namespace App\Filament\Pages;

use App\Models\DeliveryNoteItem;
use App\Models\Instrument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class InstrumentsInControl extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    
    protected static ?string $navigationLabel = 'Merila v kontroli';
    
    protected static ?string $title = 'Merila v kontroli';
    
    protected static ?string $navigationGroup = 'Merila';
    
    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.instruments-in-control';

    protected function getTableQuery(): Builder
    {
        return DeliveryNoteItem::query()
            ->with(['instrument', 'deliveryNote'])
            ->whereHas('instrument', function ($query) {
                $query->where('status', 'V_KONTROLI');
            })
            ->whereHas('deliveryNote', function ($query) {
                $query->where('archived', false);
            });
    }
    
    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('deliveryNote.number')
                    ->label('Dobavnica')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('deliveryNote.recipient')
                    ->label('Prejemnik')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('instrument.internal_id')
                    ->label('Številka merila')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('instrument.name')
                    ->label('Ime merila')
                    ->searchable()
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('check_date')
                    ->label('Datum pregleda')
                    ->date('d.m.Y')
                    ->sortable()
                    ->placeholder('Ni nastavljen'),
                
                Tables\Columns\TextColumn::make('returned_status')
                    ->label('Rezultat')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'USTREZA' => 'success',
                        'NE_USTREZA' => 'danger',
                        'IZLOCENO' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'USTREZA' => 'Ustreza',
                        'NE_USTREZA' => 'Ne ustreza',
                        'IZLOCENO' => 'Izločeno',
                        default => 'Ni nastavljen',
                    }),
                
                Tables\Columns\IconColumn::make('returned')
                    ->label('Vrnjeno')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('returned_date')
                    ->label('Datum vrnitve')
                    ->date('d.m.Y')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('delivery_note_id')
                    ->label('Dobavnica')
                    ->relationship('deliveryNote', 'number')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('update')
                    ->label('Posodobi')
                    ->icon('heroicon-o-pencil')
                    ->form([
                        Forms\Components\DatePicker::make('check_date')
                            ->label('Datum pregleda')
                            ->required()
                            ->default(now())
                            ->displayFormat('d.m.Y'),
                        
                        Forms\Components\Select::make('returned_status')
                            ->label('Rezultat kontrole')
                            ->options([
                                'USTREZA' => 'Ustreza',
                                'NE_USTREZA' => 'Ne ustreza',
                                'IZLOCENO' => 'Izločeno',
                            ])
                            ->required(),
                        
                        Forms\Components\DatePicker::make('returned_date')
                            ->label('Datum vrnitve')
                            ->default(now())
                            ->displayFormat('d.m.Y'),
                        
                        FileUpload::make('certificate')
                            ->label('Certifikat (PDF)')
                            ->acceptedFileTypes(['application/pdf'])
                            ->directory('certificates')
                            ->visibility('private')
                            ->maxSize(5120) // 5MB
                            ->downloadable()
                            ->openable(false)
                            ->helperText('Naložite nov certifikat, če je na voljo. Če v Edge zamrzne, uporabite Chrome.'),
                        
                        Forms\Components\Textarea::make('notes')
                            ->label('Opombe')
                            ->rows(3),
                    ])
                    ->action(function (DeliveryNoteItem $record, array $data) {
                        DB::transaction(function () use ($record, $data) {
                            // Posodobi DeliveryNoteItem
                            $record->update([
                                'check_date' => $data['check_date'],
                                'returned_status' => $data['returned_status'],
                                'returned_date' => $data['returned_date'] ?? now(),
                                'returned' => true,
                                'notes' => $data['notes'] ?? null,
                            ]);
                            
                            // Posodobi Instrument
                            $instrument = $record->instrument;
                            if ($instrument) {
                                $updateData = [
                                    'last_check_date' => $data['check_date'],
                                    'status' => $data['returned_status'],
                                ];
                                
                                // Če je naložen nov certifikat – stari arhivira, novi je veljavni
                                if (!empty($data['certificate'])) {
                                    $instrument->addCertificate($data['certificate'], \Carbon\Carbon::parse($data['check_date']));
                                }
                                
                                $instrument->update($updateData);
                            }
                            
                            // Preveri, ali so vsa merila posodobljena
                            $deliveryNote = $record->deliveryNote;
                            if ($deliveryNote->allItemsUpdated()) {
                                // Arhiviraj dobavnico
                                $deliveryNote->archive();
                                
                                Notification::make()
                                    ->title('Uspešno posodobljeno')
                                    ->body('Merilo je bilo posodobljeno in dobavnica je bila arhivirana.')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Uspešno posodobljeno')
                                    ->body('Merilo je bilo posodobljeno.')
                                    ->success()
                                    ->send();
                            }
                        });
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('update_all')
                        ->label('Posodobi izbrana merila')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Forms\Components\DatePicker::make('check_date')
                                ->label('Datum pregleda')
                                ->required()
                                ->default(now())
                                ->displayFormat('d.m.Y'),
                            
                            Forms\Components\Select::make('returned_status')
                                ->label('Rezultat kontrole')
                                ->options([
                                    'USTREZA' => 'Ustreza',
                                    'NE_USTREZA' => 'Ne ustreza',
                                    'IZLOCENO' => 'Izločeno',
                                ])
                                ->required(),
                            
                            Forms\Components\DatePicker::make('returned_date')
                                ->label('Datum vrnitve')
                                ->default(now())
                                ->displayFormat('d.m.Y'),
                        ])
                        ->action(function (Collection $records, array $data) {
                            DB::transaction(function () use ($records, $data) {
                                $deliveryNotes = collect();
                                
                                foreach ($records as $record) {
                                    // Posodobi DeliveryNoteItem
                                    $record->update([
                                        'check_date' => $data['check_date'],
                                        'returned_status' => $data['returned_status'],
                                        'returned_date' => $data['returned_date'] ?? now(),
                                        'returned' => true,
                                    ]);
                                    
                                    // Posodobi Instrument
                                    $instrument = $record->instrument;
                                    if ($instrument) {
                                        $updateData = [
                                            'last_check_date' => $data['check_date'],
                                            'status' => $data['returned_status'],
                                        ];
                                        
                                        $instrument->update($updateData);
                                    }
                                    
                                    $deliveryNotes->push($record->deliveryNote);
                                }
                                
                                // Preveri in arhiviraj dobavnice
                                foreach ($deliveryNotes->unique('id') as $deliveryNote) {
                                    if ($deliveryNote->allItemsUpdated()) {
                                        $deliveryNote->archive();
                                    }
                                }
                                
                                Notification::make()
                                    ->title('Uspešno posodobljeno')
                                    ->body(count($records) . ' meril je bilo posodobljenih.')
                                    ->success()
                                    ->send();
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Ni meril v kontroli')
            ->emptyStateDescription('Ko boste poslali merila na kontrolo, se bodo prikazala tukaj.');
    }
}
