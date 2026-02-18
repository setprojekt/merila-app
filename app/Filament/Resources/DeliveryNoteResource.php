<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryNoteResource\Pages;
use App\Models\DeliveryNote;
use App\Models\Instrument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Checkbox;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class DeliveryNoteResource extends Resource
{
    protected static ?string $model = DeliveryNote::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationLabel = 'Dobavnice';
    
    protected static ?string $modelLabel = 'Dobavnica';
    
    protected static ?string $pluralModelLabel = 'Dobavnice';
    
    protected static ?string $navigationGroup = 'Dobavnice';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Podatki Dobavnice')
                    ->description('Osnovni podatki o dobavnici')
                    ->schema([
                        TextInput::make('number')
                            ->label('Številka dobavnice')
                            ->disabled()
                            ->dehydrated(),
                        
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'ODPRTA' => 'Odprta',
                                'POSLANA' => 'Poslana',
                                'ZAKLJUCENA' => 'Zaključena',
                            ])
                            ->required()
                            ->default('ODPRTA')
                            ->disabled(fn ($record) => $record?->archived ?? false),
                        
                        DatePicker::make('delivery_date')
                            ->label('Datum odpreme')
                            ->displayFormat('d.m.Y')
                            ->default(now())
                            ->disabled(fn ($record) => $record?->archived ?? false),
                        
                        DatePicker::make('expected_return_date')
                            ->label('Pričakovan datum vrnitve')
                            ->displayFormat('d.m.Y')
                            ->disabled(fn ($record) => $record?->archived ?? false),
                        
                        Forms\Components\Toggle::make('archived')
                            ->label('Zaključena')
                            ->helperText('Zaključene dobavnice se ne prikazujejo v privzetem prikazu')
                            ->disabled(fn ($context, $record) => $context === 'create' || ($record?->archived ?? false))
                            ->columnSpanFull(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Pošiljatelj')
                    ->description('Podatki o pošiljatelju')
                    ->schema([
                        TextInput::make('sender_name')
                            ->label('Ime pošiljatelja')
                            ->required()
                            ->maxLength(255)
                            ->default(fn () => app(\App\Settings\Modules\InstrumentsSettings::class)->delivery_note_sender_name)
                            ->disabled(fn ($record) => $record?->archived ?? false),
                        
                        Textarea::make('sender_address')
                            ->label('Naslov pošiljatelja')
                            ->required()
                            ->rows(3)
                            ->default(fn () => app(\App\Settings\Modules\InstrumentsSettings::class)->delivery_note_sender_address)
                            ->disabled(fn ($record) => $record?->archived ?? false),
                    ])->columns(1),
                
                Forms\Components\Section::make('Prejemnik')
                    ->description('Podatki o prejemniku')
                    ->schema([
                        TextInput::make('recipient')
                            ->label('Ime prejemnika')
                            ->required()
                            ->maxLength(255)
                            ->default(fn () => app(\App\Settings\Modules\InstrumentsSettings::class)->delivery_note_recipient_name)
                            ->disabled(fn ($record) => $record?->archived ?? false),
                        
                        Textarea::make('recipient_address')
                            ->label('Naslov prejemnika')
                            ->required()
                            ->rows(3)
                            ->default(fn () => app(\App\Settings\Modules\InstrumentsSettings::class)->delivery_note_recipient_address)
                            ->disabled(fn ($record) => $record?->archived ?? false),
                    ])->columns(1),
                
                Forms\Components\Section::make('Merila na dobavnici')
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Forms\Components\Grid::make(4)
                                    ->schema([
                                        Select::make('instrument_id')
                                            ->label('Številka merila')
                                            ->relationship('instrument', 'internal_id')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->disabled()
                                            ->columnSpan(1)
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state) {
                                                    $instrument = \App\Models\Instrument::find($state);
                                                    $set('instrument_name_display', $instrument?->name);
                                                }
                                            }),
                                        
                                        TextInput::make('instrument_name_display')
                                            ->label('Naziv merila')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->afterStateHydrated(function ($component, $state, $record) {
                                                if ($record && $record->instrument) {
                                                    $component->state($record->instrument->name);
                                                }
                                            })
                                            ->columnSpan(3),
                                    ]),
                                
                                Checkbox::make('returned')
                                    ->label('Vrnjeno')
                                    ->reactive()
                                    ->disabled()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        if ($state) {
                                            $set('returned_date', now()->format('Y-m-d'));
                                        }
                                    }),
                                
                                DatePicker::make('returned_date')
                                    ->label('Datum vrnitve')
                                    ->displayFormat('d.m.Y')
                                    ->disabled()
                                    ->visible(fn ($get) => $get('returned')),
                                
                                DatePicker::make('check_date')
                                    ->label('Datum pregleda')
                                    ->displayFormat('d.m.Y')
                                    ->disabled()
                                    ->visible(fn ($get) => $get('returned')),
                                
                                Select::make('returned_status')
                                    ->label('Rezultat kontrole')
                                    ->options([
                                        'USTREZA' => 'Ustreza',
                                        'NE_USTREZA' => 'Ne ustreza',
                                        'IZLOCENO' => 'Izločeno',
                                    ])
                                    ->disabled()
                                    ->visible(fn ($get) => $get('returned')),
                                
                                Textarea::make('notes')
                                    ->label('Opombe')
                                    ->rows(2)
                                    ->disabled()
                                    ->columnSpanFull(),
                            ])
                            ->columns(1)
                            ->defaultItems(1)
                            ->addActionLabel('Dodaj merilo')
                            ->reorderable(false)
                            ->deletable(false)
                            ->addable(false)
                            ->collapsible()
                            ->itemLabel(function ($state, $record) {
                                if ($record && $record->instrument) {
                                    return $record->instrument->internal_id . ' - ' . $record->instrument->name;
                                }
                                return 'Merilo';
                            })
                            ->helperText('Merila se lahko urejajo samo preko seznama meril'),
                    ]),
                
                Forms\Components\Section::make('Dodatno')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Opombe')
                            ->rows(3)
                            ->columnSpanFull()
                            ->disabled(fn ($record) => $record?->archived ?? false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->columns([
                TextColumn::make('number')
                    ->label('Številka')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('sender_name')
                    ->label('Pošiljatelj')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('recipient')
                    ->label('Prejemnik')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(function ($record) {
                        if ($record->archived) {
                            return 'gray';
                        }
                        return match ($record->status) {
                            'ODPRTA' => 'warning',
                            'POSLANA' => 'info',
                            'ZAKLJUCENA' => 'success',
                            default => 'gray',
                        };
                    })
                    ->formatStateUsing(function ($record) {
                        if ($record->archived) {
                            return 'Zaključena';
                        }
                        return match ($record->status) {
                            'ODPRTA' => 'Odprta',
                            'POSLANA' => 'Poslana',
                            'ZAKLJUCENA' => 'Zaključena',
                            default => $record->status,
                        };
                    }),
                
                TextColumn::make('delivery_date')
                    ->label('Datum odpreme')
                    ->date('d.m.Y')
                    ->sortable(),
                
                TextColumn::make('total_instruments')
                    ->label('Št. meril')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                
                TextColumn::make('sender.full_name')
                    ->label('Ustvaril')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label('Ustvarjeno')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'ODPRTA' => 'Odprta',
                        'POSLANA' => 'Poslana',
                        'ZAKLJUCENA' => 'Zaključena',
                    ]),
            ])
            ->actions([
                ViewAction::make()
                    ->label('Prikaži'),
                EditAction::make()
                    ->label('Uredi')
                    ->disabled(fn ($record) => $record->archived),
                Action::make('print')
                    ->label('Natisni')
                    ->icon('heroicon-o-printer')
                    ->url(fn (DeliveryNote $record) => route('print.delivery-note', $record))
                    ->openUrlInNewTab(),
                DeleteAction::make()
                    ->label('Izbriši')
                    ->disabled(fn ($record) => $record->archived && !auth()->user()?->isSuperAdmin()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Izbriši izbrane'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['sender']);
        
        // Privzeto prikaži samo aktivne dobavnice, če filter ni nastavljen
        // Filter bo to preglasi
        return $query;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeliveryNotes::route('/'),
            'create' => Pages\CreateDeliveryNote::route('/create'),
            'view' => Pages\ViewDeliveryNote::route('/{record}'),
            'edit' => Pages\EditDeliveryNote::route('/{record}/edit'),
        ];
    }
}
