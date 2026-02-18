<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetencyItem extends Model
{
    protected $fillable = [
        'competency_category_id',
        'name',
        'requires_validity',
        'validity_years',
        'allow_unlimited',
        'sort_order',
        'is_hidden',
    ];

    /** Ali element omogoča "neomejeno" – samo Izpit za vožnjo viličarja (enkrat narejen, se ne obnavlja) */
    public function allowsUnlimited(): bool
    {
        return $this->allow_unlimited ?? false;
    }

    protected $casts = [
        'requires_validity' => 'boolean',
        'allow_unlimited' => 'boolean',
        'is_hidden' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(CompetencyCategory::class, 'competency_category_id');
    }

    public function matrixEntries(): HasMany
    {
        return $this->hasMany(CompetencyMatrixEntry::class, 'competency_item_id');
    }
}
