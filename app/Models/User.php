<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, LogsActivity;
    
    /**
     * Boot metoda za validacijo
     */
    protected static function boot()
    {
        parent::boot();
        
        // Preveri, da ima uporabnik vsaj en način prijave
        static::saving(function ($user) {
            // Če nima emaila in nima PIN-a
            if (empty($user->email) && empty($user->pin_code)) {
                throw new \Exception('Uporabnik mora imeti vsaj email ali PIN kodo za prijavo.');
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * NOTE: email in password sta NULLABLE (uporabniki lahko uporabljajo PIN)
     * 
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'surname',
        'employee_number',  // Zap. št.
        'function',         // Funkcija
        'email',        // nullable - uporabniki s PIN-om ne rabijo email
        'password',     // nullable - uporabniki s PIN-om ne rabijo gesla
        'pin_code',     // nullable - 4-mestni PIN (hashiran)
        'can_login_with_pin',
        'can_login_with_email',
        'force_renew_password',
        'force_renew_pin',
        'role',
        'allowed_modules',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'pin_code',
        'remember_token',
    ];
    
    /**
     * Pridobi atribute, ki naj bodo hashirani
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'pin_code' => 'hashed',
            'force_renew_password' => 'boolean',
            'force_renew_pin' => 'boolean',
            'can_login_with_pin' => 'boolean',
            'can_login_with_email' => 'boolean',
            'allowed_modules' => 'array',
            'last_login_at' => 'datetime',
            'last_activity_at' => 'datetime',
        ];
    }

    /**
     * Merila, za katera je uporabnik odgovoren
     */
    public function instruments(): HasMany
    {
        return $this->hasMany(Instrument::class);
    }

    /**
     * Vnosi v matriki usposobljenosti
     */
    public function competencyMatrixEntries(): HasMany
    {
        return $this->hasMany(CompetencyMatrixEntry::class);
    }

    /**
     * Dobavnice, ki jih je uporabnik ustvaril
     */
    public function deliveryNotes(): HasMany
    {
        return $this->hasMany(DeliveryNote::class, 'sender_id');
    }

    /**
     * Preveri, ali je uporabnik admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
    
    /**
     * Preveri, ali je uporabnik super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }
    
    /**
     * Preveri, ali ima uporabnik dostop do modula
     */
    public function canAccessModule(string $moduleId): bool
    {
        // Super admin ima dostop do vseh modulov
        if ($this->isSuperAdmin()) {
            return true;
        }
        
        // Če ni nastavljenih modulov, dovoli vse (backward compatibility)
        if (empty($this->allowed_modules)) {
            return true;
        }
        
        // Preveri, ali je modul v seznamu dovoljenih
        return in_array($moduleId, $this->allowed_modules);
    }
    
    /**
     * Pridobi polno ime uporabnika
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->name . ' ' . $this->surname);
    }
    
    /**
     * Preveri, ali je uporabnik trenutno online (aktiven v zadnjih 5 minutah)
     */
    public function isOnline(): bool
    {
        if (!$this->last_activity_at) {
            return false;
        }
        
        return $this->last_activity_at->diffInMinutes(now()) < 5;
    }
    
    /**
     * Posodobi zadnjo aktivnost
     */
    public function updateLastActivity(): void
    {
        $this->last_activity_at = now();
        $this->saveQuietly(); // Ne sproži events
    }
    
    /**
     * Zabeleži prijavo
     */
    public function recordLogin(): void
    {
        $this->last_login_at = now();
        $this->last_activity_at = now();
        $this->saveQuietly();
    }
    
    /**
     * Filament avtorizacija
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Super Admin lahko dostopa do vseh panelov
        if ($this->isSuperAdmin()) {
            return true;
        }
        
        // Super Admin panel je samo za super admine
        if ($panel->getId() === 'super-admin') {
            return false;
        }
        
        // Glavni panel (admin) - Module Dashboard za vse
        if ($panel->getId() === 'admin') {
            return true;
        }
        
        // Modul Merila - dostop za vse
        if ($panel->getId() === 'merila') {
            return true;
        }
        
        // MUS je zdaj stran znotraj admin panela, ne ločen panel
        // Prihodnji moduli - tukaj dodate pravila dostopa
        // Primer: if ($panel->getId() === 'protokoli') { return in_array($this->role, ['admin', 'user']); }
        
        return false;
    }
    
    /**
     * Konfiguracija Activity Log
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'surname', 'employee_number', 'function', 'email', 'role', 'allowed_modules'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->dontLogIfAttributesChangedOnly(['password', 'pin_code', 'remember_token'])
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => 'Uporabnik ustvarjen',
                'updated' => 'Uporabnik posodobljen',
                'deleted' => 'Uporabnik izbrisan',
                default => "Uporabnik {$eventName}",
            })
            ->useLogName('user');
    }
}
