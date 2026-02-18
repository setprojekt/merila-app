<?php

namespace App\Filament\MUS\Pages;

use App\Filament\SuperAdmin\BaseSettingsPage;
use App\Models\CompetencyItem;
use App\Settings\Modules\CompetencyMatrixSettings;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;

/**
 * Nastavitve MUS modula – prikazane v navigaciji MUS modula.
 * Uporablja iste CompetencyMatrixSettings kot Super Admin (posodobitve povsod).
 */
class MUSSettingsPage extends BaseSettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = CompetencyMatrixSettings::class;

    protected static ?string $navigationLabel = 'Nastavitve';

    protected static ?string $title = 'Nastavitve MUS – Obveščanje';

    protected static ?string $slug = 'mus/nastavitve';

    protected static ?string $navigationGroup = 'MUS';

    protected static ?int $navigationSort = 2;

    /** Prikaži samo ko smo v MUS modulu */
    public static function shouldRegisterNavigation(): bool
    {
        $path = request()->path();
        return str_contains($path, 'mus');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->isSuperAdmin() || $user->canAccessModule('mus'));
    }

    protected static string $view = 'filament.pages.settings-page';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('testEmail')
                ->label('Testiraj Pošiljanje')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Testno Pošiljanje Obvestil za Usposobljenost')
                ->modalDescription('Ali želite poslati testno obvestilo? Email bo poslan na prejemnike, ki so navedeni spodaj.')
                ->modalSubmitActionLabel('Pošlji Testni Email')
                ->action(function () {
                    try {
                        $settings = app(CompetencyMatrixSettings::class);

                        if (!$settings->send_email_notifications) {
                            Notification::make()
                                ->title('Email obvestila so onemogočena')
                                ->body('Prosimo omogočite "Pošiljaj Email Obvestila" zgoraj.')
                                ->warning()
                                ->send();
                            return;
                        }

                        if (empty($settings->notification_recipients)) {
                            Notification::make()
                                ->title('Ni prejemnikov')
                                ->body('Prosimo dodajte vsaj enega prejemnika.')
                                ->warning()
                                ->send();
                            return;
                        }

                        Artisan::call('competency:send-expiry-reminders');

                        Notification::make()
                            ->title('Uspešno poslano!')
                            ->body('Testno obvestilo je bilo poslano.')
                            ->success()
                            ->duration(5000)
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Napaka pri pošiljanju')
                            ->body('Napaka: ' . $e->getMessage())
                            ->danger()
                            ->duration(10000)
                            ->send();
                    }
                }),
        ];
    }

    public static function getSettings(): string
    {
        return CompetencyMatrixSettings::class;
    }

    protected function getSettingsFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Osnovne nastavitve modula')
                ->description('Podatki za prikaz v matriki')
                ->schema([
                    Forms\Components\TextInput::make('module_name')
                        ->label('Ime Modula')
                        ->required()
                        ->maxLength(255)
                        ->default('MUS Matrika usposobljenosti')
                        ->helperText('Prikaže se na kartici modula'),

                    Forms\Components\TextInput::make('module_number')
                        ->label('Številka Modula')
                        ->required()
                        ->maxLength(50)
                        ->default('SET 40.013')
                        ->helperText('Interna identifikacijska številka modula'),
                ])
                ->columns(2),

            Forms\Components\Section::make('Email Obvestila')
                ->description('Nastavitve za obveščanje o preteku zakonsko predpisanih usposobljenosti')
                ->schema([
                    Forms\Components\Toggle::make('send_email_notifications')
                        ->label('Pošiljaj Email Obvestila')
                        ->helperText('Obveščanje o preteku zakonsko predpisanih usposobljenosti')
                        ->default(true)
                        ->inline(false),

                    Forms\Components\Textarea::make('notification_recipients')
                        ->label('Prejemniki Obvestil')
                        ->rows(3)
                        ->placeholder('email1@example.com, email2@example.com')
                        ->helperText('Email naslovi prejemnikov, ločeni z vejico'),

                    Forms\Components\TextInput::make('notification_time')
                        ->label('Čas pošiljanja obvestil')
                        ->required()
                        ->default('08:00')
                        ->placeholder('08:00')
                        ->maxLength(5)
                        ->rule('regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/')
                        ->helperText('Ura pošiljanja obvestil. Format 00:00'),

                    Forms\Components\Select::make('notification_day_of_week')
                        ->label('Dan obveščanja')
                        ->options([
                            1 => 'Ponedeljek',
                            2 => 'Torek',
                            3 => 'Sreda',
                            4 => 'Četrtek',
                            5 => 'Petek',
                            6 => 'Sobota',
                            7 => 'Nedelja',
                        ])
                        ->default(1)
                        ->required()
                        ->helperText('Obveščanje se pošilja na ta dan v tednu'),

                    Forms\Components\TextInput::make('notification_interval_days')
                        ->label('Interval obveščanja (dni)')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->maxValue(30)
                        ->default(7)
                        ->helperText('Na koliko dni se obveščanje ponavlja (npr. 7 = enkrat na teden)'),

                    Forms\Components\TextInput::make('notification_days_before_expiry')
                        ->label('Začni obveščanje X dni pred potekom')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->maxValue(365)
                        ->default(60)
                        ->helperText('Koliko dni pred potekom veljavnosti se začne obveščanje'),
                ])
                ->columns(2),

            Forms\Components\Section::make('Frekvenca pregleda po vrstah usposobljenosti')
                ->description('Nastavite frekvenco (v letih) za vsako zakonsko predpisano usposobljenost. Sistem uporabi to za obveščanje ob približevanju poteka.')
                ->schema($this->getItemFrequencySchema())
                ->columns(2),
        ];
    }

    /** Polja za frekvenco pregleda – vsaka usposobljenost z veljavnostjo (razen viličarja) */
    protected function getItemFrequencySchema(): array
    {
        $items = CompetencyItem::where('requires_validity', true)
            ->where('allow_unlimited', false)
            ->orderBy('competency_category_id')
            ->orderBy('sort_order')
            ->get();

        $schema = [];
        foreach ($items as $item) {
            $schema[] = Forms\Components\TextInput::make("item_frequency_years.{$item->id}")
                ->label($item->name)
                ->numeric()
                ->minValue(1)
                ->maxValue(10)
                ->default($item->validity_years ?? 2)
                ->suffix('let')
                ->helperText("Frekvenca obnavljanja – na koliko let velja usposobljenost");
        }

        return $schema;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $settings = app(CompetencyMatrixSettings::class);
        $data['item_frequency_years'] = $settings->item_frequency_years ?? [];
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $years = $data['item_frequency_years'] ?? [];
        $data['item_frequency_years'] = is_array($years) ? array_filter($years, fn ($v) => $v !== null && $v !== '') : [];
        return $data;
    }
}
