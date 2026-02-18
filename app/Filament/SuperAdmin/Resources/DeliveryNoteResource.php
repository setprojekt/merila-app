<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\DeliveryNoteResource\Pages;
use App\Models\DeliveryNote;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class DeliveryNoteResource extends Resource
{
    protected static ?string $model = DeliveryNote::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Dobavnice';

    protected static ?string $modelLabel = 'Dobavnica';

    protected static ?string $pluralModelLabel = 'Dobavnice';

    protected static ?string $navigationGroup = 'Upravljanje';

    protected static ?int $navigationSort = 15;

    /** Dobavnice so samo v modulu Merila, v Super Adminu jih ne prikazujemo. */
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
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
                    ->color(fn ($record) => $record->archived ? 'gray' : match ($record->status) {
                        'ODPRTA' => 'warning',
                        'POSLANA' => 'info',
                        'ZAKLJUCENA' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($record) => $record->archived ? 'Zaključena' : match ($record->status) {
                        'ODPRTA' => 'Odprta',
                        'POSLANA' => 'Poslana',
                        'ZAKLJUCENA' => 'Zaključena',
                        default => $record->status,
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
                TextColumn::make('created_at')
                    ->label('Ustvarjeno')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('archived')
                    ->label('Zaključene')
                    ->placeholder('Vse dobavnice')
                    ->trueLabel('Samo zaključene')
                    ->falseLabel('Samo aktivne')
                    ->queries(
                        true: fn (Builder $q) => $q->where('archived', true),
                        false: fn (Builder $q) => $q->where('archived', false),
                    ),
            ])
            ->actions([
                ViewAction::make()
                    ->label('Prikaži')
                    ->url(fn (DeliveryNote $record) => '/merila/delivery-notes/' . $record->id),
                Action::make('print')
                    ->label('Natisni')
                    ->icon('heroicon-o-printer')
                    ->url(fn (DeliveryNote $record) => route('print.delivery-note', $record))
                    ->openUrlInNewTab(),
                DeleteAction::make()
                    ->label('Izbriši')
                    ->requiresConfirmation()
                    ->modalHeading('Izbriši dobavnico')
                    ->modalDescription(fn (DeliveryNote $record) => 'Ali ste prepričani? Dobavnica "' . $record->number . '" bo trajno izbrisana.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Izbriši izbrane')
                        ->requiresConfirmation()
                        ->modalHeading('Izbriši izbrane dobavnice')
                        ->modalDescription('Izbrane dobavnice bodo trajno izbrisane.'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeliveryNotes::route('/'),
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
}
