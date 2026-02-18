<?php

namespace App\Filament\SuperAdmin\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ChangePinRequired extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-key';
    
    protected static string $view = 'filament.super-admin.pages.change-pin-required';
    
    protected static ?string $title = 'Sprememba PIN-a je obvezna';
    
    protected static bool $shouldRegisterNavigation = false;
    
    public ?array $data = [];
    
    public function mount(): void
    {
        // Če uporabnik ne potrebuje spremembe PIN-a, preusmeri na dashboard
        if (!Auth::user()->force_renew_pin) {
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
                Forms\Components\Section::make('Sprememba PIN-a je obvezna')
                    ->description('Administrator je resetiral vaš PIN. Prosimo, nastavite nov PIN za nadaljevanje.')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->schema([
                        Forms\Components\TextInput::make('new_pin')
                            ->label('Nov PIN (4 številke)')
                            ->password()
                            ->revealable()
                            ->numeric()
                            ->minLength(4)
                            ->maxLength(4)
                            ->required()
                            ->placeholder('1234')
                            ->autofocus()
                            ->helperText('Izberite 4-mestno PIN številko, ki si jo boste zapomnili')
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
                                            $existingUser = \App\Models\User::where('id', '!=', Auth::id())
                                                ->whereNotNull('pin_code')
                                                ->get()
                                                ->first(function ($user) use ($value) {
                                                    return Hash::check($value, $user->pin_code);
                                                });
                                            
                                            if ($existingUser) {
                                                $fail('Ta PIN je že v uporabi pri drugem uporabniku. Prosimo, izberite drug PIN.');
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
                            ->required()
                            ->placeholder('1234')
                            ->same('new_pin')
                            ->helperText('Ponovno vnesite isti PIN za potrditev'),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }
    
    public function save(): void
    {
        $data = $this->form->getState();
        $user = Auth::user();
        
        // Preveri, da PIN ni enak prejšnjemu (če obstaja)
        if ($user->pin_code && Hash::check($data['new_pin'], $user->pin_code)) {
            Notification::make()
                ->title('Napaka')
                ->body('Nov PIN ne sme biti enak prejšnjemu PIN-u.')
                ->danger()
                ->send();
            return;
        }
        
        // Shrani nov PIN (hashiran) in odstrani zahtevo za spremembo
        $user->update([
            'pin_code' => Hash::make($data['new_pin']),
            'force_renew_pin' => false,
        ]);
        
        Notification::make()
            ->title('PIN uspešno spremenjen')
            ->body('Vaš PIN je bil uspešno spremenjen. Sedaj lahko uporabljate aplikacijo.')
            ->success()
            ->send();
        
        // Preusmeri na dashboard
        redirect()->to('/super-admin');
    }
    
    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('save')
                ->label('Shrani nov PIN')
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
