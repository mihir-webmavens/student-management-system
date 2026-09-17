<?php

namespace App\Models;

use Database\Factories\StandardFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property int $sort_order
 */
#[Fillable(['name', 'sort_order'])]
class Standard extends Model
{
    /** @use HasFactory<StandardFactory> */
    use HasFactory;

    /**
     * Get the divisions of the standard.
     *
     * @return HasMany<Division, $this>
     */
    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class)->orderBy('name');
    }
}
