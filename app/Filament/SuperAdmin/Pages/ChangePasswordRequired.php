<?php

namespace App\Filament\SuperAdmin\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequired extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';
    
    protected static string $view = 'filament.super-admin.pages.change-password-required';
    
    protected static ?string $title = 'Sprememba gesla je obvezna';
    
    protected static bool $shouldRegisterNavigation = false;
    
    public ?array $data = [];
    
    public function mount(): void
    {
        // Če uporabnik ne potrebuje spremembe gesla, preusmeri na dashboard
        if (!Auth::user()->force_renew_password) {
            redirect()->to('/super-admin')->send();
        }
    }
    
    public static function canAccess(): bool
    {
        // Vsi prijavljeni uporabniki lahko dostopajo do te strani
        return Auth::check();
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Sprememba gesla je obvezna')
                    ->description('Administrator je resetiral vaše geslo. Prosimo, nastavite novo geslo za nadaljevanje.')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->schema([
                        Forms\Components\TextInput::make('new_password')
                            ->label('Novo geslo')
                            ->password()
                            ->revealable()
                            ->required()
                            ->minLength(8)
                            ->rule(Password::default())
                            ->autofocus()
                            ->helperText('Geslo mora imeti vsaj 8 znakov'),
                        
                        Forms\Components\TextInput::make('new_password_confirmation')
                            ->label('Potrditev novega gesla')
                            ->password()
                            ->revealable()
                            ->required()
                            ->minLength(8)
                            ->same('new_password')
                            ->helperText('Ponovno vnesite isto geslo za potrditev'),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }
    
    public function save(): void
    {
        $data = $this->form->getState();
        $user = Auth::user();
        
        // Preveri, da geslo ni enako prejšnjemu (če obstaja)
        if (Hash::check($data['new_password'], $user->password)) {
            Notification::make()
                ->title('Napaka')
                ->body('Novo geslo ne sme biti enako prejšnjemu geslu.')
                ->danger()
                ->send();
            return;
        }
        
        // Shrani novo geslo in odstrani zahtevo za spremembo
        $user->update([
            'password' => Hash::make($data['new_password']),
            'force_renew_password' => false,
        ]);
        
        Notification::make()
            ->title('Geslo uspešno spremenjeno')
            ->body('Vaše geslo je bilo uspešno spremenjeno. Sedaj lahko uporabljate aplikacijo.')
            ->success()
            ->send();
        
        // Preusmeri na dashboard
        redirect()->to('/super-admin');
    }
    
    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('save')
                ->label('Shrani novo geslo')
                ->submit('save')
                ->color('primary')
                ->size('lg'),
        ];
    }
    
    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        return true;
    }
}
