<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryNoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_note_id',
        'instrument_id',
        'returned_status',
        'returned_date',
        'check_date',
        'notes',
        'returned',
    ];

    protected $casts = [
        'returned_date' => 'date',
        'check_date' => 'date',
        'returned' => 'boolean',
    ];

    /**
     * Dobavnica
     */
    public function deliveryNote(): BelongsTo
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    /**
     * Merilo
     */
    public function instrument(): BelongsTo
    {
        return $this->belongsTo(Instrument::class);
    }

    /**
     * Avtomatsko posodobi merilo ob vračilu
     */
    protected static function booted(): void
    {
        static::saving(function (DeliveryNoteItem $item) {
            // Če je merilo vrnjeno in ima check_date, posodobi instrument
            if ($item->returned && $item->check_date && $item->returned_status) {
                $instrument = $item->instrument;
                if ($instrument) {
                    $instrument->update([
                        'last_check_date' => $item->check_date,
                        'status' => $item->returned_status,
                    ]);
                    // next_check_date se bo avtomatsko posodobil preko Instrument modela
                }
            }
        });

        static::saved(function (DeliveryNoteItem $item) {
            // Preveri, ali je treba zaključiti dobavnico
            if ($item->returned) {
                $item->deliveryNote->checkAndClose();
            }
        });
    }
}
