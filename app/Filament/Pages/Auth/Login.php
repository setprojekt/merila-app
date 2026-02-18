<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    /**
     * Prilagodi formo za prijavo
     * Skladno z AUTH_LOGIC.md točka 2
     */
    protected function getForms(): array
    {
        // Preveri, katere metode prijave so na voljo
        $hasEmailUsers = User::where('can_login_with_email', true)->exists();
        $hasPinUsers = User::where('can_login_with_pin', true)->exists();
        
        // Pripravi opcije glede na to, kaj je na voljo
        $loginOptions = [];
        if ($hasEmailUsers) {
            $loginOptions['email'] = 'Prijava z E-pošto';
        }
        if ($hasPinUsers) {
            $loginOptions['pin'] = 'Prijava s PIN kodo';
        }
        
        // Določi privzeto metodo - PIN ima prednost
        $defaultMethod = $hasPinUsers ? 'pin' : 'email';
        
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        // Radio izbira načina prijave - prikaži samo če je več kot ena opcija
                        Radio::make('login_method')
                            ->label('Način prijave')
                            ->options($loginOptions)
                            ->default($defaultMethod)
                            ->inline()
                            ->live() // Reactive - spremeni formo ko se izbere
                            ->required()
                            ->visible(count($loginOptions) > 1), // Skrij, če je samo ena opcija
                        
                        // Email polje - prikaži samo če je email način na voljo in izbran
                        $this->getEmailFormComponent()
                            ->visible(fn (Get $get) => $hasEmailUsers && ($get('login_method') === 'email' || !$hasPinUsers))
                            ->required(fn (Get $get) => $hasEmailUsers && ($get('login_method') === 'email' || !$hasPinUsers)),
                        
                        // Password polje - prikaži samo če je email način na voljo in izbran
                        $this->getPasswordFormComponent()
                            ->visible(fn (Get $get) => $hasEmailUsers && ($get('login_method') === 'email' || !$hasPinUsers))
                            ->required(fn (Get $get) => $hasEmailUsers && ($get('login_method') === 'email' || !$hasPinUsers)),
                        
                        // PIN polje - prikaži samo če je PIN način na voljo in izbran
                        TextInput::make('login_pin')
                            ->label('PIN koda')
                            ->placeholder('••••')
                            ->password()
                            ->revealable(false)
                            ->minLength(4)
                            ->maxLength(4)
                            ->visible(fn (Get $get) => $hasPinUsers && ($get('login_method') === 'pin' || !$hasEmailUsers))
                            ->required(fn (Get $get) => $hasPinUsers && ($get('login_method') === 'pin' || !$hasEmailUsers))
                            ->autocomplete('off')
                            ->extraInputAttributes([
                                'inputmode' => 'numeric',
                                'pattern' => '[0-9]*',
                                'type' => 'password',
                            ]),
                        
                        // Remember me - samo za email prijavo
                        $this->getRememberFormComponent()
                            ->visible(fn (Get $get) => $hasEmailUsers && ($get('login_method') === 'email' || !$hasPinUsers)),
                    ])
                    ->statePath('data'),
            ),
        ];
    }
    
    /**
     * Prikaži link za pozabljeno geslo (samo za email prijavo)
     */
    public function hasPasswordReset(): bool
    {
        return true;
    }

    /**
     * Custom avtentikacija - ločena logika za Email vs PIN
     * Skladno z AUTH_LOGIC.md točka 2
     */
    public function authenticate(): ?\Filament\Http\Responses\Auth\Contracts\LoginResponse
    {
        try {
            $data = $this->form->getState();
            
            // Določi privzeto metodo, če ni izbrane
            $hasEmailUsers = User::where('can_login_with_email', true)->exists();
            $hasPinUsers = User::where('can_login_with_pin', true)->exists();
            $defaultMethod = $hasPinUsers ? 'pin' : 'email';
            
            $loginMethod = $data['login_method'] ?? $defaultMethod;
            
            if ($loginMethod === 'email') {
                // SCENARIJ EMAIL: Standardna avtentikacija
                return $this->authenticateWithEmail($data);
            } else {
                // SCENARIJ PIN: Iteracija čez uporabnike
                return $this->authenticateWithPin($data);
            }
            
        } catch (\Illuminate\Validation\ValidationException $exception) {
            throw $exception;
        }
    }
    
    /**
     * Prijava z email in geslom (standardna metoda)
     */
    protected function authenticateWithEmail(array $data): ?\Filament\Http\Responses\Auth\Contracts\LoginResponse
    {
        // Standardna Laravel avtentikacija
        if (!Auth::attempt(
            ['email' => $data['email'], 'password' => $data['password']],
            $data['remember'] ?? false
        )) {
            $this->throwFailureValidationException();
        }
        
        $user = Auth::user();
        
        // Preveri, ali ima uporabnik omogočeno prijavo z emailom
        if (!$user->can_login_with_email) {
            Auth::logout();
            throw ValidationException::withMessages([
                'data.email' => 'Prijava z e-pošto ni omogočena za tega uporabnika. Uporabite PIN prijavo.',
            ]);
        }
        
        session()->regenerate();
        
        // Zabeleži prijavo
        $user->recordLogin();
        
        return app(\Filament\Http\Responses\Auth\Contracts\LoginResponse::class);
    }
    
    /**
     * Prijava s PIN kodo (iteracija čez uporabnike z nastavljenim PIN-om)
     * Skladno z AUTH_LOGIC.md točka 3, scenarij PIN
     */
    protected function authenticateWithPin(array $data): ?\Filament\Http\Responses\Auth\Contracts\LoginResponse
    {
        $inputPin = $data['login_pin'] ?? '';
        
        // Preveri, da je PIN 4-mestna številka
        if (strlen($inputPin) !== 4 || !is_numeric($inputPin)) {
            throw ValidationException::withMessages([
                'data.login_pin' => 'PIN mora biti točno 4-mestna številka.',
            ]);
        }
        
        // 1. Pridobi VSE uporabnike, ki imajo nastavljen pin_code IN omogočeno PIN prijavo
        $usersWithPin = User::whereNotNull('pin_code')
            ->where('can_login_with_pin', true)
            ->get();
        
        if ($usersWithPin->isEmpty()) {
            throw ValidationException::withMessages([
                'data.login_pin' => 'Prijava s PIN-om trenutno ni na voljo.',
            ]);
        }
        
        // 2. Zanka čez uporabnike
        foreach ($usersWithPin as $user) {
            // 3. Preveri Hash::check za vsakega uporabnika
            if (Hash::check($inputPin, $user->pin_code)) {
                // 4. Če najdeš ujemanje -> prijavi uporabnika
                Auth::login($user, false); // PIN prijava nima "remember me"
                session()->regenerate();
                
                // Zabeleži prijavo
                $user->recordLogin();
                
                return app(\Filament\Http\Responses\Auth\Contracts\LoginResponse::class);
            }
        }
        
        // 5. Če zanka pride do konca brez uspeha -> napaka
        throw ValidationException::withMessages([
            'data.login_pin' => 'Napačen PIN.',
        ]);
    }

    /**
     * Throw a failed authentication validation exception.
     */
    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.email' => __('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }
}
