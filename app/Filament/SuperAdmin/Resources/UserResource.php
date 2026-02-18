<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Validation\Rules\Password;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Uporabniki';

    protected static ?string $modelLabel = 'Uporabnik';

    protected static ?string $pluralModelLabel = 'Uporabniki';

    protected static ?string $navigationGroup = 'Upravljanje';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Osnovni podatki')
                    ->schema([
                        TextInput::make('name')
                            ->label('Ime')
                            ->required()
                            ->maxLength(255),
                        
                        TextInput::make('surname')
                            ->label('Priimek')
                            ->maxLength(255),
                        
                        TextInput::make('employee_number')
                            ->label('Zap. št.')
                            ->maxLength(20)
                            ->helperText('Zaporedna številka zaposlenega'),
                        
                        TextInput::make('function')
                            ->label('Funkcija')
                            ->maxLength(100)
                            ->helperText('Vloga zaposlenega, npr. direktor, brusilec'),
                    ])->columns(2),
                
                Section::make('Prijava z E-pošto')
                    ->description('Nastavite prijavo z emailom in geslom')
                    ->schema([
                        Forms\Components\Toggle::make('can_login_with_email')
                            ->label('Omogoči prijavo z E-pošto')
                            ->helperText('Uporabnik se lahko prijavi z emailom in geslom')
                            ->default(true)
                            ->reactive()
                            ->columnSpanFull(),
                        
                        TextInput::make('email')
                            ->label('E-pošta')
                            ->email()
                            ->nullable()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Obvezno za prijavo z emailom')
                            ->visible(fn (Forms\Get $get): bool => $get('can_login_with_email'))
                            ->required(fn (Forms\Get $get): bool => $get('can_login_with_email')),
                        
                        TextInput::make('password')
                            ->label('Geslo')
                            ->password()
                            ->nullable()
                            ->required(fn (Forms\Get $get, string $operation): bool => 
                                $operation === 'create' && $get('can_login_with_email') === true
                            )
                            ->dehydrated(fn ($state) => filled($state))
                            ->rule(Password::default())
                            ->helperText('Obvezno za prijavo z emailom. Pustite prazno, če ne želite spremeniti.')
                            ->minLength(8)
                            ->visible(fn (Forms\Get $get): bool => $get('can_login_with_email')),
                    ])
                    ->columns(2)
                    ->collapsible(),
                
                Section::make('Prijava s PIN-om')
                    ->description('Nastavite prijavo s 4-mestno PIN številko')
                    ->schema([
                        Forms\Components\Toggle::make('can_login_with_pin')
                            ->label('Omogoči prijavo s PIN-om')
                            ->helperText('Uporabnik se lahko prijavi s 4-mestno PIN številko')
                            ->default(false)
                            ->reactive()
                            ->columnSpanFull(),
                        
                        TextInput::make('pin_code')
                            ->label('PIN (4 mestna številka)')
                            ->password()
                            ->revealable()
                            ->numeric()
                            ->minLength(4)
                            ->maxLength(4)
                            ->placeholder('1234')
                            ->helperText('4-mestna PIN številka za prijavo. Pustite prazno, če ne želite spremeniti.')
                            ->visible(fn (Forms\Get $get): bool => $get('can_login_with_pin'))
                            ->required(fn (Forms\Get $get, $record): bool => $get('can_login_with_pin') && !$record)
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(function ($state, $record) {
                                if (!filled($state)) {
                                    return null;
                                }
                                
                                // Preveri, ali PIN že obstaja pri drugem uporabniku
                                $existingUser = \App\Models\User::where('id', '!=', $record?->id ?? 0)
                                    ->get()
                                    ->first(function ($user) use ($state) {
                                        return $user->pin_code && \Illuminate\Support\Facades\Hash::check($state, $user->pin_code);
                                    });
                                
                                if ($existingUser) {
                                    throw new \Exception('Ta PIN je že v uporabi. Prosimo, izberite drug PIN.');
                                }
                                
                                return \Illuminate\Support\Facades\Hash::make($state);
                            }),
                    ])
                    ->columns(1)
                    ->collapsible(),
                
                Section::make('Hitro Ponastavitev')
                    ->description('Resetirajte PIN ali geslo uporabnika')
                    ->schema([
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('reset_pin')
                                ->label('Resetiraj PIN')
                                ->icon('heroicon-o-key')
                                ->color('warning')
                                ->requiresConfirmation()
                                ->modalHeading('Resetiraj PIN')
                                ->modalDescription('Ali ste prepričani, da želite resetirati PIN uporabnika?')
                                ->modalSubmitActionLabel('Resetiraj')
                                ->form([
                                    TextInput::make('new_pin')
                                        ->label('Nov PIN (4 številke)')
                                        ->password()
                                        ->revealable()
                                        ->numeric()
                                        ->minLength(4)
                                        ->maxLength(4)
                                        ->required()
                                        ->placeholder('1234'),
                                ])
                                ->action(function (array $data, $record) {
                                    $record->update([
                                        'pin_code' => \Illuminate\Support\Facades\Hash::make($data['new_pin']),
                                        'can_login_with_pin' => true,
                                        'force_renew_pin' => true,
                                    ]);
                                    
                                    \Filament\Notifications\Notification::make()
                                        ->title('PIN resetiran')
                                        ->body('PIN uporabnika je bil uspešno resetiran. Uporabnik bo moral spremeniti PIN ob naslednji prijavi.')
                                        ->success()
                                        ->send();
                                })
                                ->visible(fn ($record) => $record !== null),
                            
                            Forms\Components\Actions\Action::make('reset_password')
                                ->label('Resetiraj Geslo')
                                ->icon('heroicon-o-lock-closed')
                                ->color('danger')
                                ->requiresConfirmation()
                                ->modalHeading('Resetiraj Geslo')
                                ->modalDescription('Ali ste prepričani, da želite resetirati geslo uporabnika?')
                                ->modalSubmitActionLabel('Resetiraj')
                                ->form([
                                    TextInput::make('new_password')
                                        ->label('Novo geslo')
                                        ->password()
                                        ->revealable()
                                        ->required()
                                        ->minLength(8)
                                        ->placeholder('********'),
                                    
                                    Forms\Components\Toggle::make('force_renew')
                                        ->label('Vsili spremembo ob naslednji prijavi')
                                        ->helperText('Uporabnik bo moral spremeniti geslo ob naslednji prijavi')
                                        ->default(true),
                                ])
                                ->action(function (array $data, $record) {
                                    $record->update([
                                        'password' => \Illuminate\Support\Facades\Hash::make($data['new_password']),
                                        'force_renew_password' => $data['force_renew'] ?? false,
                                    ]);
                                    
                                    \Filament\Notifications\Notification::make()
                                        ->title('Geslo resetirano')
                                        ->body('Geslo uporabnika je bilo uspešno resetirano.' . ($data['force_renew'] ? ' Uporabnik bo moral spremeniti geslo ob naslednji prijavi.' : ''))
                                        ->success()
                                        ->send();
                                })
                                ->visible(fn ($record) => $record !== null),
                        ])
                    ])
                    ->visible(fn ($record) => $record !== null)
                    ->collapsible(),
                
                Section::make('Vloga in Pravice')
                    ->schema([
                        Select::make('role')
                            ->label('Vloga')
                            ->options([
                                'super_admin' => 'Super Admin',
                                'admin' => 'Admin',
                                'user' => 'Uporabnik',
                                'viewer' => 'Ogledovalec',
                            ])
                            ->required()
                            ->default('user'),
                    ])->columns(1),
                
                Section::make('Dostop do modulov')
                    ->description('Izberite module, do katerih ima uporabnik dostop. Če ni izbran noben modul, ima dostop do vseh.')
                    ->schema([
                        Forms\Components\CheckboxList::make('allowed_modules')
                            ->label('Dovoljeni moduli')
                            ->options([
                                'merila' => 'Merila',
                                'mus' => 'MUS Matrika usposobljenosti',
                                'protokoli' => 'Kalibracijski Protokoli (kmalu)',
                                'porocila' => 'Poročila (kmalu)',
                            ])
                            ->descriptions([
                                'merila' => 'Upravljanje meril in dobavnic',
                                'mus' => 'Matrika usposobljenosti zaposlenih',
                                'protokoli' => 'Ustvarjanje protokolov (še ni implementirano)',
                                'porocila' => 'Generiranje poročil (še ni implementirano)',
                            ])
                            ->columns(2)
                            ->gridDirection('row')
                            ->helperText('Super Admin ima vedno dostop do vseh modulov'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee_number')
                    ->label('Zap. št.')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Ime')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn (User $record): string => $record->full_name),

                TextColumn::make('function')
                    ->label('Funkcija')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('E-pošta')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('can_login_with_pin')
                    ->label('PIN prijava')
                    ->boolean()
                    ->trueIcon('heroicon-o-key')
                    ->falseIcon('heroicon-o-envelope')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn (User $record): string => 
                        $record->can_login_with_pin 
                            ? 'Prijava s PIN: ' . ($record->pin_code ?? 'Ni nastavljen') 
                            : 'Prijava z e-pošto'
                    ),

                TextColumn::make('role')
                    ->label('Vloga')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'admin' => 'warning',
                        'user' => 'success',
                        'viewer' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'super_admin' => 'Super Admin',
                        'admin' => 'Admin',
                        'user' => 'Uporabnik',
                        'viewer' => 'Ogledovalec',
                        default => $state,
                    })
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_online')
                    ->label('Status')
                    ->boolean()
                    ->getStateUsing(fn (User $record): bool => $record->isOnline())
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn (User $record): string => 
                        $record->isOnline() ? 'Prijavljen' : 'Neprijavljen'
                    ),

                TextColumn::make('last_login_at')
                    ->label('Zadnja prijava')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->description(fn (User $record): ?string => 
                        $record->last_login_at 
                            ? $record->last_login_at->diffForHumans() 
                            : null
                    )
                    ->placeholder('Nikoli'),

                TextColumn::make('created_at')
                    ->label('Ustvarjeno')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Posodobljeno')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Vloga')
                    ->options([
                        'super_admin' => 'Super Admin',
                        'admin' => 'Admin',
                        'user' => 'Uporabnik',
                        'viewer' => 'Ogledovalec',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
