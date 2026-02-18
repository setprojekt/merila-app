<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;

class EditProfile extends BaseEditProfile
{
    protected static string $view = 'filament-panels::pages.auth.edit-profile';
    
    /**
     * Prepiši email komponento, da dovoljuje prazne emaile (nullable)
     */
    protected function getEmailFormComponent(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('email')
            ->label('E-pošta')
            ->email()
            ->nullable()
            ->unique(ignoreRecord: true)
            ->maxLength(255)
            ->helperText('E-pošta ni obvezna, če uporabljate prijavo s PIN-om');
    }
    
    /**
     * Custom Edit Profile Form
     * Skladno z AUTH_LOGIC.md točka 5
     */
    public function form(Form $form): Form
    {
        $user = auth()->user();
        
        return $form
            ->schema([
                // 1. Ime in Email (standardno)
                Forms\Components\Section::make('Podatki profila')
                    ->description('Vaši osebni podatki')
                    ->schema([
                        $this->getNameFormComponent(),
                        
                        Forms\Components\TextInput::make('surname')
                            ->label('Priimek')
                            ->maxLength(255),
                        
                        $this->getEmailFormComponent(),
                    ])
                    ->columns(2),
                
                // Prikaz trenutnih nastavitev prijave
                Forms\Components\Section::make('Trenutne nastavitve prijave')
                    ->description('Pregled vaših trenutno aktivnih metod prijave')
                    ->schema([
                        Forms\Components\Placeholder::make('login_methods')
                            ->label('Aktivne metode prijave')
                            ->content(function () use ($user) {
                                $methods = [];
                                if ($user->can_login_with_email && $user->email) {
                                    $methods[] = '✓ Prijava z E-pošto (' . $user->email . ')';
                                }
                                if ($user->can_login_with_pin && $user->pin_code) {
                                    $methods[] = '✓ Prijava s PIN kodo';
                                }
                                
                                return empty($methods) 
                                    ? 'Nimate nastavljene nobene metode prijave' 
                                    : implode("\n", $methods);
                            }),
                    ])
                    ->collapsible(),
                
                // 2. Sekcija "Varnost"
                Forms\Components\Section::make('Varnost')
                    ->description('Spremenite svoje geslo ali PIN kodo')
                    ->schema([
                        // Novo geslo (in potrditev)
                        Forms\Components\TextInput::make('new_password')
                            ->label('Novo geslo')
                            ->password()
                            ->revealable()
                            ->rule(Password::default())
                            ->minLength(8)
                            ->dehydrated(false)
                            ->helperText('Pustite prazno, če ne želite spremeniti gesla'),
                        
                        Forms\Components\TextInput::make('new_password_confirmation')
                            ->label('Potrditev novega gesla')
                            ->password()
                            ->revealable()
                            ->same('new_password')
                            ->dehydrated(false)
                            ->requiredWith('new_password')
                            ->helperText('Ponovno vnesite isto geslo za potrditev'),
                        
                        // Nov PIN (in potrditev) -> Validacija: točno 4 številke
                        Forms\Components\TextInput::make('new_pin')
                            ->label('Nov PIN (4 številke)')
                            ->password()
                            ->revealable()
                            ->numeric()
                            ->length(4)
                            ->minLength(4)
                            ->maxLength(4)
                            ->placeholder('1234')
                            ->helperText('Pustite prazno, če ne želite spremeniti PIN-a')
                            ->dehydrated(false)
                            ->rules([
                                function () {
                                    return function (string $attribute, $value, \Closure $fail) {
                                        if (filled($value)) {
                                            // Validacija: točno 4 številke
                                            if (strlen($value) !== 4 || !is_numeric($value)) {
                                                $fail('PIN mora biti točno 4-mestna številka.');
                                                return;
                                            }
                                            
                                            // Preveri, če PIN že obstaja pri drugem uporabniku
                                            $existingUser = \App\Models\User::where('id', '!=', auth()->id())
                                                ->get()
                                                ->first(function ($user) use ($value) {
                                                    return $user->pin_code && Hash::check($value, $user->pin_code);
                                                });
                                            
                                            if ($existingUser) {
                                                $fail('Ta PIN je že v uporabi.');
                                            }
                                        }
                                    };
                                },
                            ]),
                        
                        Forms\Components\TextInput::make('new_pin_confirmation')
                            ->label('Potrditev novega PIN-a')
                            ->password()
                            ->revealable()
                            ->numeric()
                            ->minLength(4)
                            ->maxLength(4)
                            ->placeholder('1234')
                            ->same('new_pin')
                            ->requiredWith('new_pin')
                            ->helperText('Ponovno vnesite isti PIN za potrditev')
                            ->dehydrated(false),
                        
                        // Trenutno geslo (za potrditev sprememb)
                        Forms\Components\TextInput::make('current_password')
                            ->label('Trenutno geslo (ali PIN)')
                            ->password()
                            ->revealable()
                            ->required(fn (Forms\Get $get): bool => 
                                filled($get('new_password')) || filled($get('new_pin'))
                            )
                            ->helperText('Za potrditev sprememb vnesite trenutno geslo ali PIN kodo')
                            ->dehydrated(false)
                            ->live(),
                    ])
                    ->columns(2),
            ]);
    }
    
    /**
     * Pri shranjevanju poskrbi, da se PIN in Geslo hashirata, če sta bila spremenjena
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $currentPasswordInput = request()->input('current_password');
        $user = auth()->user();
        
        // Preveri trenutno geslo ali PIN (katerikoli je nastavljen)
        if (filled($currentPasswordInput)) {
            $validPassword = false;
            
            // Preveri geslo, če ga ima uporabnik
            if ($user->password && Hash::check($currentPasswordInput, $user->password)) {
                $validPassword = true;
            }
            
            // Preveri PIN, če ga ima uporabnik
            if (!$validPassword && $user->pin_code && Hash::check($currentPasswordInput, $user->pin_code)) {
                $validPassword = true;
            }
            
            if (!$validPassword) {
                throw ValidationException::withMessages([
                    'data.current_password' => 'Trenutno geslo ali PIN ni pravilno.',
                ]);
            }
        }
        
        // Hashiraj novo geslo, če je bilo vnešeno
        if (request()->filled('new_password')) {
            $data['password'] = Hash::make(request()->input('new_password'));
        }
        
        // Hashiraj nov PIN, če je bil vnešen
        if (request()->filled('new_pin')) {
            $data['pin_code'] = Hash::make(request()->input('new_pin'));
        }
        
        // Odstrani začasna polja
        unset($data['new_password'], $data['new_password_confirmation']);
        unset($data['new_pin'], $data['new_pin_confirmation']);
        unset($data['current_password']);
        
        return $data;
    }
}
