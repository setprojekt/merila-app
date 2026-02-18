<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Carbon\Carbon;

class Instrument extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'internal_id',
        'name',
        'type',
        'location',
        'department',
        'frequency_years',
        'last_check_date',
        'next_check_date',
        'status',
        'certificate_path',
        'archived',
        'user_id',
    ];

    protected $casts = [
        'last_check_date' => 'date',
        'next_check_date' => 'date',
        'frequency_years' => 'decimal:2',
        'archived' => 'boolean',
    ];

    /**
     * Odgovoren uporabnik
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Postavke dobavnic, kjer je to merilo
     */
    public function deliveryNoteItems(): HasMany
    {
        return $this->hasMany(DeliveryNoteItem::class);
    }

    /**
     * Zgodovina certifikatov – pretekli so arhivirani, novi so veljavni
     */
    public function certificates(): HasMany
    {
        return $this->hasMany(InstrumentCertificate::class)->orderByDesc('created_at');
    }

    /**
     * Aktualni (veljavni) certifikat
     */
    public function currentCertificate(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(InstrumentCertificate::class)->where('status', InstrumentCertificate::STATUS_VALID)->latest();
    }

    /**
     * Dodaj nov certifikat – stari preide v arhiviran, novi je veljavni
     */
    public function addCertificate(string $certificatePath, ?\Carbon\Carbon $checkDate = null): InstrumentCertificate
    {
        $cert = null;
        \DB::transaction(function () use ($certificatePath, $checkDate, &$cert) {
            $this->certificates()->update(['status' => InstrumentCertificate::STATUS_ARCHIVED]);
            $cert = $this->certificates()->create([
                'certificate_path' => $certificatePath,
                'check_date' => $checkDate ?? $this->last_check_date,
                'status' => InstrumentCertificate::STATUS_VALID,
            ]);
            $this->update(['certificate_path' => $certificatePath]);
        });

        return $cert;
    }

    /**
     * Izračunaj naslednji datum pregleda
     */
    public function calculateNextCheckDate(): ?Carbon
    {
        if (!$this->last_check_date || !$this->frequency_years) {
            return null;
        }

        return $this->last_check_date->copy()->addYears((int) $this->frequency_years)
            ->addMonths((int) (($this->frequency_years - (int) $this->frequency_years) * 12));
    }

    /**
     * Avtomatsko posodobi next_check_date in status ob shranjevanju
     */
    protected static function booted(): void
    {
        static::saving(function (Instrument $instrument) {
            // Optimizacija: preveri samo, če so relevantni podatki dirty
            $isDateDirty = $instrument->isDirty('last_check_date') || $instrument->isDirty('frequency_years');
            
            // Izračunaj next_check_date samo, če se je spremenil last_check_date ali frequency_years
            if ($isDateDirty && $instrument->last_check_date && $instrument->frequency_years) {
                $calculatedNextDate = $instrument->calculateNextCheckDate();
                if ($calculatedNextDate) {
                    $instrument->next_check_date = $calculatedNextDate;
                }
            }

            // Avtomatsko posodobi status samo, če je potrebno
            if ($instrument->next_check_date && 
                $instrument->status !== 'IZLOCENO' && 
                $instrument->status !== 'V_KONTROLI' &&
                ($instrument->isDirty('next_check_date') || $isDateDirty)) {
                
                // Posodobi status samo, če je next_check_date pretečen ali veljaven
                $instrument->status = $instrument->next_check_date->isPast() ? 'NE_USTREZA' : 'USTREZA';
            }

            // Avtomatsko nastavi archived, če je status IZLOCENO
            if ($instrument->status === 'IZLOCENO' && !$instrument->archived) {
                $instrument->archived = true;
            }
        });
        
        // Cache invalidation - osveži cache ob shranjevanju merila
        static::saved(function () {
            \App\Filament\Widgets\InstrumentsStatsOverview::clearCache();
        });
        
        static::deleted(function () {
            \App\Filament\Widgets\InstrumentsStatsOverview::clearCache();
        });
    }

    /**
     * Scope za aktivna merila (ne arhivirana, ne v kontroli)
     */
    public function scopeActive($query)
    {
        return $query->where('archived', false)
            ->where('status', '!=', 'V_KONTROLI');
    }

    /**
     * Scope za merila v roku 30 dni ali pretečena
     */
    public function scopeNeedsAttention($query)
    {
        $today = Carbon::today();
        $thirtyDaysFromNow = $today->copy()->addDays(30);

        return $query->where('archived', false)
            ->where('status', '!=', 'V_KONTROLI')
            ->where('status', '!=', 'IZLOCENO')
            ->where(function ($q) use ($today, $thirtyDaysFromNow) {
                $q->where('next_check_date', '<=', $thirtyDaysFromNow)
                  ->orWhere('next_check_date', '<', $today);
            });
    }

    /**
     * Scope za pretečena merila
     */
    public function scopeExpired($query)
    {
        return $query->where('archived', false)
            ->where('status', '!=', 'V_KONTROLI')
            ->where('next_check_date', '<', Carbon::today());
    }

    /**
     * Scope za merila v opozorilu (<= 30 dni)
     */
    public function scopeWarning($query)
    {
        $today = Carbon::today();
        $thirtyDaysFromNow = $today->copy()->addDays(30);

        return $query->where('archived', false)
            ->where('status', '!=', 'V_KONTROLI')
            ->where('status', '!=', 'IZLOCENO')
            ->whereBetween('next_check_date', [$today, $thirtyDaysFromNow]);
    }

    /**
     * Scope za veljavna merila (> 30 dni)
     */
    public function scopeValid($query)
    {
        return $query->where('archived', false)
            ->where('status', '!=', 'V_KONTROLI')
            ->where('status', '!=', 'IZLOCENO')
            ->where('next_check_date', '>', Carbon::today()->addDays(30));
    }
    
    /**
     * Konfiguracija Activity Log
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'internal_id',
                'name',
                'type',
                'location',
                'department',
                'frequency_years',
                'last_check_date',
                'next_check_date',
                'status',
                'archived',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->dontLogIfAttributesChangedOnly(['updated_at']) // Ne logiraj samo timestamp sprememb
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => 'Merilo ustvarjeno',
                'updated' => 'Merilo posodobljeno',
                'deleted' => 'Merilo izbrisano',
                default => "Merilo {$eventName}",
            })
            ->useLogName('instrument');
    }
}
