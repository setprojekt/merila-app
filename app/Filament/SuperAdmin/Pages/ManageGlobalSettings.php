<?php

namespace App\Filament\SuperAdmin\Pages;

use App\Filament\SuperAdmin\BaseSettingsPage;
use App\Settings\GlobalSettings;
use Filament\Forms;

class ManageGlobalSettings extends BaseSettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = GlobalSettings::class;

    protected static ?string $navigationLabel = 'Globalne Nastavitve';

    protected static ?string $title = 'Globalne Nastavitve';

    protected static ?string $navigationGroup = 'Nastavitve';

    protected static ?int $navigationSort = 10;

    protected static ?string $slug = 'nastavitve/globalne';

    public static function getSettings(): string
    {
        return GlobalSettings::class;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['auto_logout_timeout'])) {
            $data['auto_logout_timeout'] = (int) ($data['auto_logout_timeout'] ?? 0);
        }
        return $data;
    }

    protected function getSettingsFormSchema(): array
    {
        return [
                Forms\Components\Section::make('Podatki Podjetja')
                    ->description('Osnovni podatki o podjetju, ki se uporabljajo v aplikaciji')
                    ->schema([
                        Forms\Components\TextInput::make('app_name')
                            ->label('Ime Aplikacije')
                            ->required()
                            ->maxLength(255)
                            ->default('SET Intranet')
                            ->helperText('Prikaže se v glavi aplikacije'),
                        
                        Forms\Components\TextInput::make('company_name')
                            ->label('Ime Podjetja')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Textarea::make('company_address')
                            ->label('Naslov Podjetja')
                            ->required()
                            ->rows(3)
                            ->maxLength(500),
                        
                        Forms\Components\TextInput::make('company_phone')
                            ->label('Telefon Podjetja')
                            ->tel()
                            ->required()
                            ->maxLength(50),
                        
                        Forms\Components\TextInput::make('company_email')
                            ->label('Email Podjetja')
                            ->email()
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Email Nastavitve')
                    ->description('Nastavitve za pošiljanje emailov iz aplikacije')
                    ->schema([
                        Forms\Components\TextInput::make('mail_from_address')
                            ->label('Email Od (From Address)')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->helperText('Email naslov, ki se bo prikazal kot pošiljatelj'),
                        
                        Forms\Components\TextInput::make('mail_from_name')
                            ->label('Ime Pošiljatelja (From Name)')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Ime, ki se bo prikazalo kot pošiljatelj'),
                        
                        Forms\Components\TextInput::make('notification_email')
                            ->label('Email za Obvestila')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->helperText('Email naslov za prejemanje sistemskih obvestil. Geslo za SMTP nastavite v .env (MAIL_PASSWORD).'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Varnostne Nastavitve')
                    ->description('Nastavitve varnosti in avtentikacije za vse module')
                    ->schema([
                        Forms\Components\TextInput::make('auto_logout_timeout')
                            ->label('Avtomatska Odjava po Nedejavnosti (sekunde)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(86400) // Max 24 ur
                            ->default(1800) // 30 minut
                            ->suffix('sekund')
                            ->helperText('Čas nedejavnosti (v sekundah) preden se uporabnik avtomatsko odjavi. 0 = onemogočeno. Priporočeno: 1800 (30 min)'),
                    ])
                    ->columns(1)
                    ->collapsible(),
        ];
    }
}
