<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstrumentCertificate extends Model
{
    protected $fillable = [
        'instrument_id',
        'certificate_path',
        'check_date',
        'status',
    ];

    protected $casts = [
        'check_date' => 'date',
    ];

    public const STATUS_VALID = 'veljavni';
    public const STATUS_ARCHIVED = 'arhiviran';

    public function instrument(): BelongsTo
    {
        return $this->belongsTo(Instrument::class);
    }

    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    public function isValid(): bool
    {
        return $this->status === self::STATUS_VALID;
    }
}
