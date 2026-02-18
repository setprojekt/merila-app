<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class Profile extends Page
{
    /**
     * Panel merila uporablja EditProfile prek ->profile(), zato te strani ne prikazujemo.
     * Prepreči napako "Route [filament.merila.pages.profile] not defined".
     */
    public static function canAccess(): bool
    {
        $panel = Filament::getCurrentPanel();
        return $panel && $panel->getId() !== 'merila';
    }
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    
    protected static string $view = 'filament.pages.profile';
    
    protected static ?string $title = 'Moj Profil';
    
    protected static ?string $navigationLabel = 'Profil';
    
    protected static ?int $navigationSort = 100;
    
    public ?array $data = [];
    
    public function mount(): void
    {
        $this->form->fill([
            'name' => Auth::user()->name,
            'surname' => Auth::user()->surname,
            'email' => Auth::user()->email,
        ]);
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Osnovni podatki')
                    ->description('Vaši osnovni podatki')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Ime')
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('surname')
                            ->label('Priimek')
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('email')
                            ->label('E-pošta')
                            ->email()
                            ->disabled(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Spremeni Geslo')
                    ->description('Spremenite svoje geslo za prijavo')
                    ->schema([
                        Forms\Components\TextInput::make('current_password')
                            ->label('Trenutno geslo')
                            ->password()
                            ->revealable()
                            ->required()
                            ->rule('current_password'),
                        
                        Forms\Components\TextInput::make('new_password')
                            ->label('Novo geslo')
                            ->password()
                            ->revealable()
                            ->required()
                            ->minLength(8)
                            ->same('new_password_confirmation'),
                        
                        Forms\Components\TextInput::make('new_password_confirmation')
                            ->label('Potrditev novega gesla')
                            ->password()
                            ->revealable()
                            ->required()
                            ->minLength(8),
                    ])
                    ->columns(1),
                
                Forms\Components\Section::make('Spremeni PIN')
                    ->description('Spremenite svoj 4-mestni PIN za prijavo')
                    ->schema([
                        Forms\Components\TextInput::make('new_pin')
                            ->label('Nov PIN (4 številke)')
                            ->password()
                            ->revealable()
                            ->numeric()
                            ->minLength(4)
                            ->maxLength(4)
                            ->placeholder('1234')
                            ->helperText('Pustite prazno, če ne želite spremeniti PIN-a')
                            ->rule(function () {
                                return function (string $attribute, $value, \Closure $fail) {
                                    if (empty($value)) {
                                        return;
                                    }
                                    
                                    // Preveri, ali PIN že obstaja pri drugem uporabniku
                                    $existingUser = \App\Models\User::where('id', '!=', Auth::id())
                                        ->get()
                                        ->first(function ($user) use ($value) {
                                            return $user->pin_code && Hash::check($value, $user->pin_code);
                                        });
                                    
                                    if ($existingUser) {
                                        $fail('Ta PIN je že v uporabi. Prosimo, izberite drug PIN.');
                                    }
                                };
                            }),
                        
                        Forms\Components\TextInput::make('new_pin_confirmation')
                            ->label('Potrditev novega PIN-a')
                            ->password()
                            ->revealable()
                            ->numeric()
                            ->minLength(4)
                            ->maxLength(4)
                            ->placeholder('1234')
                            ->same('new_pin')
                            ->requiredWith('new_pin'),
                    ])
                    ->columns(2)
                    ->visible(fn () => Auth::user()->can_login_with_pin),
            ])
            ->statePath('data');
    }
    
    public function save(): void
    {
        $data = $this->form->getState();
        $user = Auth::user();
        
        // Preveri trenutno geslo
        if (!Hash::check($data['current_password'], $user->password)) {
            Notification::make()
                ->title('Napaka')
                ->body('Trenutno geslo ni pravilno.')
                ->danger()
                ->send();
            return;
        }
        
        // Spremeni geslo
        if (filled($data['new_password'])) {
            $user->password = Hash::make($data['new_password']);
        }
        
        // Spremeni PIN (če je omogočen in vnešen)
        if ($user->can_login_with_pin && filled($data['new_pin'] ?? null)) {
            $user->pin_code = Hash::make($data['new_pin']);
        }
        
        $user->save();
        
        Notification::make()
            ->title('Uspešno')
            ->body('Vaši podatki so bili uspešno posodobljeni.')
            ->success()
            ->send();
        
        // Počisti formo
        $this->form->fill([
            'name' => $user->name,
            'surname' => $user->surname,
            'email' => $user->email,
            'current_password' => null,
            'new_password' => null,
            'new_password_confirmation' => null,
            'new_pin' => null,
            'new_pin_confirmation' => null,
        ]);
    }
    
    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('save')
                ->label('Shrani spremembe')
                ->submit('save'),
        ];
    }
}
