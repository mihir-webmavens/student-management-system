<?php

namespace App\Models;

use Database\Factories\TeachingAllocationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $teacher_profile_id
 * @property int $division_id
 * @property int $subject_id
 */
#[Fillable(['teacher_profile_id', 'division_id', 'subject_id'])]
class TeachingAllocation extends Model
{
    /** @use HasFactory<TeachingAllocationFactory> */
    use HasFactory;

    /**
     * Get the teacher of the allocation.
     *
     * @return BelongsTo<TeacherProfile, $this>
     */
    public function teacherProfile(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class);
    }

    /**
     * Get the division of the allocation.
     *
     * @return BelongsTo<Division, $this>
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Get the subject of the allocation.
     *
     * @return BelongsTo<Subject, $this>
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
