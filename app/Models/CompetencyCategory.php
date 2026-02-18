<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetencyCategory extends Model
{
    protected $fillable = ['name', 'sort_order'];

    public function items(): HasMany
    {
        return $this->hasMany(CompetencyItem::class, 'competency_category_id')->orderBy('sort_order');
    }
}
