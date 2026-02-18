<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetencyMatrixEntry extends Model
{
    protected $fillable = [
        'user_id',
        'competency_item_id',
        'status',
        'valid_until',
    ];

    protected $casts = [
        'valid_until' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function competencyItem(): BelongsTo
    {
        return $this->belongsTo(CompetencyItem::class);
    }
}
