<?php

namespace App\Models;

use Database\Factories\SubjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $code
 */
#[Fillable(['name', 'code'])]
class Subject extends Model
{
    /** @use HasFactory<SubjectFactory> */
    use HasFactory;

    /**
     * Get the teacher and division allocations of the subject.
     *
     * @return HasMany<TeachingAllocation, $this>
     */
    public function teachingAllocations(): HasMany
    {
        return $this->hasMany(TeachingAllocation::class);
    }
}
