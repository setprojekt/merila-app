<?php

namespace App\Filament\Resources\InstrumentResource\RelationManagers;

use App\Models\InstrumentCertificate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class CertificatesRelationManager extends RelationManager
{
    protected static string $relationship = 'certificates';

    protected static ?string $title = 'Zgodovina certifikatov';

    protected static ?string $modelLabel = 'Certifikat';

    protected static ?string $pluralModelLabel = 'Certifikati';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('certificate_path')
                    ->label('Certifikat (PDF)')
                    ->acceptedFileTypes(['application/pdf'])
                    ->directory('certificates')
                    ->visibility('private')
                    ->maxSize(5120)
                    ->openable(false)
                    ->required()
                    ->helperText('Max 5 MB. Če nalaganje v Edge zamrzne, uporabite Chrome ali manjši PDF.'),
                Forms\Components\DatePicker::make('check_date')
                    ->label('Datum pregleda')
                    ->displayFormat('d.m.Y'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('certificate_path')
            ->columns([
                Tables\Columns\TextColumn::make('check_date')
                    ->label('Datum pregleda')
                    ->date('d.m.Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === InstrumentCertificate::STATUS_VALID ? 'Veljavni' : 'Arhiviran')
                    ->color(fn (string $state): string => $state === InstrumentCertificate::STATUS_VALID ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dodan')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Dodaj nov certifikat')
                    ->modalHeading('Dodaj nov certifikat')
                    ->modalDescription('Pretekli certifikat bo arhiviran, novi bo veljavni.')
                    ->mutateFormDataUsing(function (array $data): array {
                        return $data;
                    })
                    ->using(function (array $data): InstrumentCertificate {
                        return $this->getOwnerRecord()->addCertificate(
                            $data['certificate_path'],
                            isset($data['check_date']) ? \Carbon\Carbon::parse($data['check_date']) : null
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Prenesi')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (InstrumentCertificate $record): string => route('certificate.download', ['certificate' => $record->id]))
                    ->openUrlInNewTab()
                    ->visible(fn (InstrumentCertificate $record): bool => Storage::disk('local')->exists($record->certificate_path)),
            ])
            ->bulkActions([
                //
            ]);
    }
}
