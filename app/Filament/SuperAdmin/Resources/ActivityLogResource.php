<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\ActivityLogResource\Pages;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Dnevnik Aktivnosti';

    protected static ?string $modelLabel = 'Aktivnost';

    protected static ?string $pluralModelLabel = 'Aktivnosti';

    protected static ?string $navigationGroup = 'Sistem';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('log_name')
                    ->label('Tip')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'default' => 'gray',
                        'instrument' => 'success',
                        'delivery_note' => 'info',
                        'user' => 'warning',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),

                TextColumn::make('description')
                    ->label('Opis')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('subject_type')
                    ->label('Model')
                    ->formatStateUsing(fn ($state) => class_basename($state))
                    ->sortable(),

                TextColumn::make('subject_id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('causer.full_name')
                    ->label('Uporabnik')
                    ->formatStateUsing(fn ($state) => $state ?? 'Sistem')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('causer', function (Builder $q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('surname', 'like', "%{$search}%");
                        });
                    })
                    ->sortable()
                    ->default('Sistem'),

                TextColumn::make('event')
                    ->label('Dogodek')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'info',
                        'deleted' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'created' => 'Ustvarjeno',
                        'updated' => 'Posodobljeno',
                        'deleted' => 'Izbrisano',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Datum')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('log_name')
                    ->label('Tip')
                    ->options([
                        'default' => 'Privzeto',
                        'instrument' => 'Merila',
                        'delivery_note' => 'Dobavnice',
                        'user' => 'Uporabniki',
                    ]),

                SelectFilter::make('event')
                    ->label('Dogodek')
                    ->options([
                        'created' => 'Ustvarjeno',
                        'updated' => 'Posodobljeno',
                        'deleted' => 'Izbrisano',
                    ]),

                SelectFilter::make('causer_id')
                    ->label('Uporabnik')
                    ->options(function () {
                        return \App\Models\User::query()
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn ($u) => [$u->id => $u->full_name])
                            ->toArray();
                    })
                    ->searchable()
                    ->preload(),

                Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label('Od'),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->label('Do'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalContent(function ($record) {
                        return view('filament.resources.activity-log.view-activity', [
                            'record' => $record,
                        ]);
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('60s'); // Osveži vsakih 60 sekund (optimizirano)
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('causer');
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
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
