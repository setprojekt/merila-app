<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InstrumentResource\Pages;
use App\Filament\Resources\InstrumentResource\RelationManagers;
use App\Models\Instrument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class InstrumentResource extends Resource
{
    protected static ?string $model = Instrument::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    
    protected static ?string $navigationLabel = 'Merila';
    
    protected static ?string $modelLabel = 'Merilo';
    
    protected static ?string $pluralModelLabel = 'Merila';
    
    protected static ?string $navigationGroup = 'Merila';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Osnovni podatki')
                    ->schema([
                        TextInput::make('internal_id')
                            ->label('Številka merila')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        
                        TextInput::make('name')
                            ->label('Ime merila')
                            ->required()
                            ->maxLength(255),
                        
                        TextInput::make('type')
                            ->label('Vrsta merila')
                            ->maxLength(255),
                        
                        TextInput::make('location')
                            ->label('Lokacija')
                            ->maxLength(255),
                        
                        TextInput::make('department')
                            ->label('Oddelek')
                            ->maxLength(255),
                    ])->columns(2),
                
                Forms\Components\Section::make('Pregled')
                    ->schema([
                        Forms\Components\TextInput::make('frequency_years')
                            ->label('Frekvenca pregleda (leta)')
                            ->numeric()
                            ->default(2.00)
                            ->step(0.5)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // Posodobi next_check_date in status samo, če obstaja last_check_date
                                $lastCheckDate = $get('last_check_date');
                                if (!$lastCheckDate || !$state) {
                                    return;
                                }
                                
                                try {
                                    $frequency = (float) $state;
                                    if ($frequency <= 0) {
                                        return;
                                    }
                                    
                                    // Poenostavljena parsanje - DatePicker že vrne Carbon ali Y-m-d string
                                    $lastDate = is_string($lastCheckDate) 
                                        ? Carbon::parse($lastCheckDate) 
                                        : $lastCheckDate;
                                    
                                    if (!$lastDate) {
                                        return;
                                    }
                                    
                                    // Izračunaj next_check_date
                                    $nextDate = $lastDate->copy()
                                        ->addYears((int) $frequency)
                                        ->addMonths((int) (($frequency - (int) $frequency) * 12));
                                    
                                    $set('next_check_date', $nextDate->format('Y-m-d'));
                                    
                                    // Avtomatsko posodobi status samo, če ni poseben status
                                    $currentStatus = $get('status');
                                    if ($currentStatus !== 'IZLOCENO' && $currentStatus !== 'V_KONTROLI') {
                                        $set('status', $nextDate->isPast() ? 'NE_USTREZA' : 'USTREZA');
                                    }
                                } catch (\Exception $e) {
                                    // Ignoriraj napako pri parsanju
                                }
                            }),
                        
                        DatePicker::make('last_check_date')
                            ->label('Datum zadnjega pregleda')
                            ->displayFormat('d.m.Y')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if (!$state) {
                                    $set('next_check_date', null);
                                    return;
                                }
                                
                                try {
                                    // Poenostavljena parsanje - DatePicker že vrne Carbon ali Y-m-d string
                                    $lastDate = is_string($state) ? Carbon::parse($state) : $state;
                                    
                                    if (!$lastDate) {
                                        return;
                                    }
                                    
                                    $frequency = (float) ($get('frequency_years') ?? 2.0);
                                    if ($frequency <= 0) {
                                        return;
                                    }
                                    
                                    // Izračunaj next_check_date
                                    $nextDate = $lastDate->copy()
                                        ->addYears((int) $frequency)
                                        ->addMonths((int) (($frequency - (int) $frequency) * 12));
                                    
                                    $set('next_check_date', $nextDate->format('Y-m-d'));
                                    
                                    // Avtomatsko posodobi status samo, če ni poseben status
                                    $currentStatus = $get('status');
                                    if ($currentStatus !== 'IZLOCENO' && $currentStatus !== 'V_KONTROLI') {
                                        $set('status', $nextDate->isPast() ? 'NE_USTREZA' : 'USTREZA');
                                    }
                                } catch (\Exception $e) {
                                    // Ignoriraj napako pri parsanju
                                }
                            }),
                        
                        DatePicker::make('next_check_date')
                            ->label('Datum naslednjega pregleda')
                            ->displayFormat('d.m.Y')
                            ->disabled()
                            ->dehydrated(),
                        
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'USTREZA' => 'Ustreza',
                                'NE_USTREZA' => 'Ne ustreza',
                                'IZLOCENO' => 'Izločeno',
                                'V_KONTROLI' => 'V kontroli',
                            ])
                            ->required()
                            ->default('USTREZA')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Če je status IZLOCENO, avtomatsko arhiviraj
                                if ($state === 'IZLOCENO') {
                                    $set('archived', true);
                                }
                            }),
                        
                        Toggle::make('archived')
                            ->label('Arhivirano')
                            ->helperText('Arhivirana merila se ne prikazujejo v privzetem prikazu'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table, bool $skipArchivedFilter = false): Table
    {
        $tableInstance = $table
            ->deferLoading()
            ->columns([
                TextColumn::make('internal_id')
                    ->label('Številka merila')
                    ->searchable()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        // Numerično sortiranje: izvleci prvo številko iz stringa
                        // Uporabi LENGTH za določitev, kje se številka začne
                        return $query->orderByRaw(
                            'CAST(
                                CASE 
                                    WHEN internal_id REGEXP "^[0-9]+$" THEN internal_id
                                    WHEN internal_id REGEXP "^[0-9]+" THEN 
                                        SUBSTRING(internal_id, 1, 
                                            CASE 
                                                WHEN LOCATE(" ", internal_id) > 0 THEN LOCATE(" ", internal_id) - 1
                                                WHEN LOCATE("/", internal_id) > 0 THEN LOCATE("/", internal_id) - 1
                                                ELSE LENGTH(internal_id)
                                            END
                                        )
                                    ELSE "0"
                                END AS UNSIGNED
                            ) ' . $direction . ', internal_id ' . $direction
                        );
                    })
                    ->toggleable(),
                
                TextColumn::make('name')
                    ->label('Ime')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->toggleable(),
                
                TextColumn::make('type')
                    ->label('Vrsta')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('location')
                    ->label('Lokacija')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('department')
                    ->label('Oddelek')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'USTREZA' => 'success',
                        'NE_USTREZA' => 'danger',
                        'IZLOCENO' => 'gray',
                        'V_KONTROLI' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'USTREZA' => 'Ustreza',
                        'NE_USTREZA' => 'Ne ustreza',
                        'IZLOCENO' => 'Izločeno',
                        'V_KONTROLI' => 'V kontroli',
                        default => $state,
                    })
                    ->toggleable(),
                
                TextColumn::make('next_check_date')
                    ->label('Velja do')
                    ->date('d.m.Y')
                    ->sortable()
                    ->color(function ($record) {
                        if (!$record->next_check_date) {
                            return 'gray';
                        }
                        
                        $daysUntilExpiry = Carbon::today()->diffInDays($record->next_check_date, false);
                        
                        if ($daysUntilExpiry < 0) {
                            return 'danger'; // Pretečeno - rdeča
                        } elseif ($daysUntilExpiry <= 30) {
                            return 'warning'; // Opozorilo - rumena
                        } else {
                            return 'success'; // Veljavno - zelena
                        }
                    })
                    ->icon(function ($record) {
                        if (!$record->next_check_date) {
                            return null;
                        }
                        
                        $daysUntilExpiry = Carbon::today()->diffInDays($record->next_check_date, false);
                        
                        if ($daysUntilExpiry < 0) {
                            return 'heroicon-o-exclamation-triangle';
                        } elseif ($daysUntilExpiry <= 30) {
                            return 'heroicon-o-exclamation-circle';
                        }
                        
                        return null;
                    })
                    ->toggleable(),
                
                TextColumn::make('days_until_expiry')
                    ->label('Dni do poteka')
                    ->getStateUsing(function ($record) {
                        if (!$record->next_check_date) {
                            return 'N/A';
                        }
                        
                        $days = Carbon::today()->diffInDays($record->next_check_date, false);
                        
                        if ($days < 0) {
                            return abs($days) . ' dni pretečeno';
                        } elseif ($days === 0) {
                            return 'Poteče danes';
                        } else {
                            return $days . ' dni';
                        }
                    })
                    ->color(function ($record) {
                        if (!$record->next_check_date) {
                            return 'gray';
                        }
                        
                        $days = Carbon::today()->diffInDays($record->next_check_date, false);
                        
                        if ($days < 0) {
                            return 'danger';
                        } elseif ($days <= 30) {
                            return 'warning';
                        } else {
                            return 'success';
                        }
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('next_check_date', $direction);
                    })
                    ->toggleable(),
                
                TextColumn::make('last_check_date')
                    ->label('Datum zadnjega pregleda')
                    ->date('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label('Ustvarjeno')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label('Posodobljeno')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'USTREZA' => 'Ustreza',
                        'NE_USTREZA' => 'Ne ustreza',
                        'IZLOCENO' => 'Izločeno',
                        'V_KONTROLI' => 'V kontroli',
                    ]),
                
                Filter::make('needs_attention')
                    ->label('Potrebuje pozornost')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->needsAttention()),
                
                Filter::make('expired')
                    ->label('Pretečeno')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->expired()),
                
                Filter::make('warning')
                    ->label('Opozorilo (≤30 dni)')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->warning()),
                
                Filter::make('show_valid')
                    ->label('Veljavna merila (>30 dni)')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->valid()),
                
                Filter::make('show_archived')
                    ->label('Prikaži arhivirana')
                    ->toggle()
                    ->query(function (Builder $query, array $data): Builder {
                        // Ko je filter aktiviran, odstrani privzeti where('archived', false) 
                        // in dodaj where('archived', true)
                        if ($data['value'] ?? false) {
                            // Odstrani zadnji where za archived (iz modifyQueryUsing)
                            $wheres = $query->getQuery()->wheres;
                            $query->getQuery()->wheres = array_filter($wheres, function($where) {
                                return !($where['type'] === 'Basic' && 
                                        $where['column'] === 'archived' && 
                                        $where['value'] === false);
                            });
                            return $query->where('archived', true);
                        }
                        return $query;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Uredi')
                    ->url(fn (Instrument $record) => InstrumentResource::getUrl('edit', ['record' => $record])),
                Tables\Actions\DeleteAction::make()
                    ->label('Izbriši'),
            ])
            ->recordUrl(function ($record) {
                // Optimizacija: cache URL
                return InstrumentResource::getUrl('edit', ['record' => $record]);
            })
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('send_to_control')
                        ->label('Pošlji na kontrolo')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Pošlji merila na kontrolo')
                        ->modalDescription('Sistem bo avtomatsko ustvaril dobavnico s podatki iz Nastavitev Meril.')
                        ->action(function (Collection $records) {
                            \Illuminate\Support\Facades\DB::transaction(function () use ($records) {
                                // Pridobi nastavitve
                                $settings = app(\App\Settings\Modules\InstrumentsSettings::class);
                                
                                // Ustvari dobavnico z vsemi podatki iz nastavitev
                                $deliveryNote = \App\Models\DeliveryNote::create([
                                    'sender_id' => Auth::id(),
                                    'sender_name' => $settings->delivery_note_sender_name,
                                    'sender_address' => $settings->delivery_note_sender_address,
                                    'recipient' => $settings->delivery_note_recipient_name,
                                    'recipient_address' => $settings->delivery_note_recipient_address,
                                    'status' => 'ODPRTA',
                                    'delivery_date' => now(),
                                ]);
                                
                                // Dodaj merila na dobavnico in spremeni status
                                foreach ($records as $instrument) {
                                    if ($instrument->status !== 'V_KONTROLI' && !$instrument->archived) {
                                        // Dodaj na dobavnico
                                        $deliveryNote->items()->create([
                                            'instrument_id' => $instrument->id,
                                        ]);
                                        
                                        // Spremeni status merila
                                        $instrument->update([
                                            'status' => 'V_KONTROLI',
                                        ]);
                                    }
                                }
                                
                                Notification::make()
                                    ->title('Dobavnica ustvarjena')
                                    ->body('Merila so bila poslana na kontrolo.')
                                    ->success()
                                    ->send();
                                
                                // Preusmeri na dobavnico
                                return redirect(\App\Filament\Resources\DeliveryNoteResource::getUrl('edit', ['record' => $deliveryNote]));
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                    
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Izbriši izbrana'),
                ])
                    ->dropdown(false),
            ])
            ->defaultSort('internal_id', 'asc');
        
        // Dodaj modifyQueryUsing samo, če ne preskačemo filtra
        if (!$skipArchivedFilter) {
            $tableInstance->modifyQueryUsing(function (Builder $query) {
                // Privzeti prikaz: samo ne arhivirana merila
                // Filter "Prikaži arhivirana" bo to preglasi z where('archived', true)
                $query->where('archived', false);
            });
        }
        
        return $tableInstance;
    }

    public static function getEloquentQuery(): Builder
    {
        // Optimizirana poizvedba - samo withTrashed
        // Archived filter je v Filter ključu in upravlja archived status
        return parent::getEloquentQuery()->withTrashed();
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CertificatesRelationManager::class,
            RelationManagers\ActivitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInstruments::route('/'),
            'create' => Pages\CreateInstrument::route('/create'),
            'view' => Pages\ViewInstrument::route('/{record}'),
            'edit' => Pages\EditInstrument::route('/{record}/edit'),
        ];
    }
}
