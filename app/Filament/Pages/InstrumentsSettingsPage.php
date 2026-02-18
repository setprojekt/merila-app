<?php

namespace App\Filament\Pages;

use App\Filament\SuperAdmin\BaseSettingsPage;
use App\Settings\Modules\InstrumentsSettings;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;

/**
 * Nastavitve modula Merila – prikazane v navigaciji modula Merila.
 * Uporablja iste InstrumentsSettings kot Super Admin (posodobitve povsod).
 */
class InstrumentsSettingsPage extends BaseSettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = InstrumentsSettings::class;

    protected static ?string $navigationLabel = 'Nastavitve';

    protected static ?string $title = 'Nastavitve Modula Meril';

    protected static ?string $slug = 'nastavitve';

    protected static ?string $navigationGroup = 'Nastavitve';

    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.pages.settings-page';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->isSuperAdmin() || $user->canAccessModule('merila'));
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('testEmail')
                ->label('Testiraj Pošiljanje')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Testno Pošiljanje Obvestil za Merila')
                ->modalDescription('Ali želite poslati testno obvestilo? Email bo poslan na prejemnike, ki so navedeni spodaj v polju "Prejemniki Obvestil".')
                ->modalSubmitActionLabel('Pošlji Testni Email')
                ->action(function () {
                    try {
                        $instrumentsSettings = app(InstrumentsSettings::class);

                        if (!$instrumentsSettings->send_email_notifications) {
                            Notification::make()
                                ->title('Email obvestila za merila so onemogočena')
                                ->body('Prosimo omogočite "Pošiljaj Email Obvestila" zgoraj.')
                                ->warning()
                                ->send();
                            return;
                        }

                        if (empty($instrumentsSettings->notification_recipients)) {
                            Notification::make()
                                ->title('Ni prejemnikov')
                                ->body('Prosimo dodajte vsaj enega prejemnika v polje "Prejemniki Obvestil".')
                                ->warning()
                                ->send();
                            return;
                        }

                        Artisan::call('instruments:send-reminders');

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
        return InstrumentsSettings::class;
    }

    protected function getSettingsFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Identifikacija Modula')
                ->description('Ime in številka modula za prikaz v aplikaciji')
                ->schema([
                    Forms\Components\TextInput::make('module_name')
                        ->label('Ime Modula')
                        ->required()
                        ->maxLength(255)
                        ->default('Merila')
                        ->helperText('Prikaže se na kartici modula'),

                    Forms\Components\TextInput::make('module_number')
                        ->label('Številka Modula')
                        ->required()
                        ->maxLength(50)
                        ->default('70.0001')
                        ->helperText('Interna identifikacijska številka modula'),
                ])
                ->columns(2),

            Forms\Components\Section::make('Dobavnica Nastavitve')
                ->description('Privzeti podatki za dobavnice meril')
                ->schema([
                    Forms\Components\TextInput::make('delivery_note_sender_name')
                        ->label('Ime Pošiljatelja')
                        ->required()
                        ->maxLength(255)
                        ->helperText('Privzeto ime pošiljatelja na dobavnicah'),

                    Forms\Components\Textarea::make('delivery_note_sender_address')
                        ->label('Naslov Pošiljatelja')
                        ->required()
                        ->rows(3)
                        ->maxLength(500),

                    Forms\Components\TextInput::make('delivery_note_recipient_name')
                        ->label('Ime Prejemnika')
                        ->required()
                        ->maxLength(255)
                        ->helperText('Privzeto ime prejemnika na dobavnicah'),

                    Forms\Components\Textarea::make('delivery_note_recipient_address')
                        ->label('Naslov Prejemnika')
                        ->required()
                        ->rows(3)
                        ->maxLength(500),
                ])
                ->columns(2),

            Forms\Components\Section::make('Email Obvestila')
                ->description('Nastavitve za email obvestila specifična za merila')
                ->schema([
                    Forms\Components\Toggle::make('send_email_notifications')
                        ->label('Pošiljaj Email Obvestila')
                        ->helperText('Omogoči/onemogoči email obvestila za merila')
                        ->default(true)
                        ->inline(false),

                    Forms\Components\Textarea::make('notification_recipients')
                        ->label('Prejemniki Obvestil')
                        ->required()
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
                        ->helperText('Ura pošiljanja obvestil. Format 00:00 (samo številke, npr. 08:00)')
                        ->extraInputAttributes(['inputmode' => 'numeric', 'pattern' => '[0-9:]*', 'autocomplete' => 'off'])
                        ->dehydrateStateUsing(function ($state) {
                            if (empty($state)) {
                                return '08:00';
                            }
                            if (preg_match('/^(\d{1,2}):(\d{1,2})$/', $state, $m)) {
                                return sprintf('%02d:%02d', (int) $m[1], (int) $m[2]);
                            }
                            return $state;
                        }),

                    Forms\Components\Select::make('notification_day_of_week')
                        ->label('Dan v tednu (obvestila 6–30 dni)')
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
                        ->helperText('Ob obdobju 6–30 dni do preteka se obvestila pošiljajo samo na ta dan. Ob 5 dneh in manj se pošilja vsak dan. Ob ponedeljkih se pošlje vsa merila v kriterijih.'),
                ])
                ->columns(1),

            Forms\Components\Section::make('Opozorila in Statusi')
                ->description('Konfiguracija časovnih mejnih vrednosti za statuse meril')
                ->schema([
                    Forms\Components\TextInput::make('expiry_warning_days')
                        ->label('Dnevi za "Opozorilo" Status')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->maxValue(365)
                        ->default(30)
                        ->helperText('Št. dni pred potekom, ko merilo dobi "warning" status (rumeno)'),

                    Forms\Components\TextInput::make('expiry_alert_days')
                        ->label('Dnevi za "Poteklo" Status')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->maxValue(30)
                        ->default(7)
                        ->helperText('Št. dni pred potekom, ko merilo dobi "expired" status (rdeče)'),
                ])
                ->columns(2),

            Forms\Components\Section::make('Arhiviranje')
                ->description('Avtomatsko arhiviranje poteklih meril')
                ->schema([
                    Forms\Components\Toggle::make('auto_archive_expired')
                        ->label('Avtomatsko Arhiviraj Potekla Merila')
                        ->helperText('Samodejno arhivira merila po določenem času od poteka')
                        ->default(false)
                        ->inline(false)
                        ->reactive(),

                    Forms\Components\TextInput::make('auto_archive_after_days')
                        ->label('Arhiviraj Po (dnevi)')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->maxValue(365)
                        ->default(90)
                        ->helperText('Število dni po poteku, ko se merilo avtomatsko arhivira')
                        ->visible(fn (Forms\Get $get) => $get('auto_archive_expired') === true),
                ])
                ->columns(2),
        ];
    }
}
