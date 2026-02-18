<?php

namespace App\Filament\Resources\InstrumentResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    protected static ?string $title = 'Aktivnosti';

    protected static ?string $modelLabel = 'Aktivnost';

    protected static ?string $pluralModelLabel = 'Aktivnosti';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->label('Opis')
                    ->searchable(),
                Tables\Columns\TextColumn::make('event')
                    ->label('Dogodek')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'created' => 'Ustvarjeno',
                        'updated' => 'Posodobljeno',
                        'deleted' => 'Izbrisano',
                        default => $state ?? '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'info',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('causer.full_name')
                    ->label('Uporabnik')
                    ->formatStateUsing(fn ($state) => $state ?: 'Sistem'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Datum')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([])
            ->actions([])
            ->bulkActions([])
            ->modifyQueryUsing(fn ($query) => $query->inLog('instrument'));
    }
}
