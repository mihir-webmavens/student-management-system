<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\TeacherProfile;
use App\Models\TeachingAllocation;
use Illuminate\Database\Seeder;

class TeachingAllocationSeeder extends Seeder
{
    /**
     * Allocate a teacher to every subject of every division.
     *
     * The teacher is picked from those whose specialization matches the subject, rotating between them
     * division by division, so each teacher teaches the same subject across several divisions.
     * Division subjects that already have a teacher are left untouched.
     */
    public function run(): void
    {
        $teachersBySpecialization = TeacherProfile::query()
            ->where('status', 'active')
            ->orderBy('id')
            ->get()
            ->groupBy('specialization')
            ->map->values();

        $divisions = Division::query()->with('subjects')->orderBy('id')->get()->values();

        foreach ($divisions as $index => $division) {
            foreach ($division->subjects as $subject) {
                $teachers = $teachersBySpecialization->get($subject->name);

                if ($teachers === null) {
                    continue;
                }

                TeachingAllocation::firstOrCreate(
                    ['division_id' => $division->id, 'subject_id' => $subject->id],
                    ['teacher_profile_id' => $teachers[$index % $teachers->count()]->id],
                );
            }
        }
    }
}
