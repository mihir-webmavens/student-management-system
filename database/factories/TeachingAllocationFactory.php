<?php

namespace Database\Factories;

use App\Models\Division;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\TeachingAllocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeachingAllocation>
 */
class TeachingAllocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'teacher_profile_id' => TeacherProfile::factory(),
            'division_id' => Division::factory(),
            'subject_id' => Subject::factory(),
        ];
    }
}
