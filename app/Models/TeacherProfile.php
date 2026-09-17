<?php

namespace App\Models;

use Database\Factories\TeacherProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $employee_code
 * @property string|null $phone
 * @property Carbon|null $date_of_birth
 * @property string|null $gender
 * @property string|null $address
 * @property Carbon|null $joining_date
 * @property string|null $qualification
 * @property string|null $specialization
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'user_id',
    'employee_code',
    'phone',
    'date_of_birth',
    'gender',
    'address',
    'joining_date',
    'qualification',
    'specialization',
    'status',
])]
class TeacherProfile extends Model
{
    /** @use HasFactory<TeacherProfileFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'joining_date' => 'date',
        ];
    }

    /**
     * Get the user the profile belongs to.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subject and division allocations of the teacher.
     *
     * @return HasMany<TeachingAllocation, $this>
     */
    public function teachingAllocations(): HasMany
    {
        return $this->hasMany(TeachingAllocation::class);
    }
}
