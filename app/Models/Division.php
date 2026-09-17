<?php

namespace App\Models;

use Database\Factories\DivisionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $standard_id
 * @property string $name
 */
#[Fillable(['standard_id', 'name'])]
class Division extends Model
{
    /** @use HasFactory<DivisionFactory> */
    use HasFactory;

    /**
     * Get the standard the division belongs to.
     *
     * @return BelongsTo<Standard, $this>
     */
    public function standard(): BelongsTo
    {
        return $this->belongsTo(Standard::class);
    }

    /**
     * Get the student profiles in the division.
     *
     * @return HasMany<StudentProfile, $this>
     */
    public function studentProfiles(): HasMany
    {
        return $this->hasMany(StudentProfile::class);
    }

    /**
     * Get the teacher and subject allocations of the division.
     *
     * @return HasMany<TeachingAllocation, $this>
     */
    public function teachingAllocations(): HasMany
    {
        return $this->hasMany(TeachingAllocation::class);
    }
}
