<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class DeliveryNote extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'number',
        'sender_id',
        'sender_name',
        'sender_address',
        'recipient',
        'recipient_address',
        'status',
        'delivery_date',
        'expected_return_date',
        'actual_return_date',
        'notes',
        'total_instruments',
        'archived',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'expected_return_date' => 'date',
        'actual_return_date' => 'date',
        'total_instruments' => 'integer',
        'archived' => 'boolean',
    ];

    /**
     * Uporabnik, ki je ustvaril dobavnico
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Postavke dobavnice
     */
    public function items(): HasMany
    {
        return $this->hasMany(DeliveryNoteItem::class);
    }

    /**
     * Merila na dobavnici
     */
    public function instruments()
    {
        return $this->belongsToMany(Instrument::class, 'delivery_note_items')
            ->withPivot(['returned_status', 'returned_date', 'check_date', 'notes', 'returned'])
            ->withTimestamps();
    }

    /**
     * Avtomatsko generiraj številko dobavnice
     */
    protected static function booted(): void
    {
        static::creating(function (DeliveryNote $deliveryNote) {
            if (empty($deliveryNote->number)) {
                $lastNumber = static::max('id') ?? 0;
                $deliveryNote->number = 'DN-' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
            }

            if (empty($deliveryNote->sender_id)) {
                $deliveryNote->sender_id = Auth::id();
            }
        });

        static::saved(function (DeliveryNote $deliveryNote) {
            // Posodobi total_instruments
            $deliveryNote->total_instruments = $deliveryNote->items()->count();
            $deliveryNote->saveQuietly();
        });

        // Ob brisanju dobavnice: merila, ki niso bila poslana/pregledana, vrni v predhodni status
        static::deleting(function (DeliveryNote $deliveryNote) {
            foreach ($deliveryNote->items as $item) {
                $instrument = $item->instrument;
                if (!$instrument || $instrument->status !== 'V_KONTROLI') {
                    continue;
                }
                // Če merilo ni bilo vrnjeno in pregledano, vrni status na osnovi next_check_date
                if (!$item->returned || !$item->check_date || !$item->returned_status) {
                    $newStatus = $instrument->next_check_date && $instrument->next_check_date->isFuture()
                        ? 'USTREZA'
                        : 'NE_USTREZA';
                    $instrument->update(['status' => $newStatus]);
                }
            }
        });
    }

    /**
     * Preveri, ali so vsa merila vrnjena
     */
    public function allItemsReturned(): bool
    {
        $itemsCount = $this->items()->count();
        if ($itemsCount === 0) {
            return false;
        }

        return $this->items()->where('returned', true)->count() === $itemsCount;
    }

    /**
     * Avtomatsko zaključi dobavnico, če so vsa merila vrnjena
     */
    public function checkAndClose(): void
    {
        if ($this->allItemsReturned() && $this->status !== 'ZAKLJUCENA') {
            $this->update([
                'status' => 'ZAKLJUCENA',
                'actual_return_date' => now(),
            ]);
        }
    }

    /**
     * Preveri, ali so vsa merila posodobljena (imajo check_date in returned_status)
     */
    public function allItemsUpdated(): bool
    {
        $itemsCount = $this->items()->count();
        if ($itemsCount === 0) {
            return false;
        }

        return $this->items()
            ->whereNotNull('check_date')
            ->whereNotNull('returned_status')
            ->count() === $itemsCount;
    }

    /**
     * Arhiviraj dobavnico
     */
    public function archive(): void
    {
        $this->update([
            'archived' => true,
        ]);
    }

    /**
     * Scope za aktivne dobavnice (ne arhivirane)
     */
    public function scopeActive($query)
    {
        return $query->where('archived', false);
    }

    /**
     * Scope za arhivirane dobavnice
     */
    public function scopeArchived($query)
    {
        return $query->where('archived', true);
    }
    
    /**
     * Konfiguracija Activity Log
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'delivery_note_number',
                'recipient',
                'delivery_date',
                'expected_return_date',
                'status',
                'notes',
                'archived',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->dontLogIfAttributesChangedOnly(['updated_at']) // Ne logiraj samo timestamp sprememb
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => 'Dobavnica ustvarjena',
                'updated' => 'Dobavnica posodobljena',
                'deleted' => 'Dobavnica izbrisana',
                default => "Dobavnica {$eventName}",
            })
            ->useLogName('delivery_note');
    }
}
