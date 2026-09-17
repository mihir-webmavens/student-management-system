<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\Standard;
use App\Models\Subject;
use App\Models\TeacherProfile;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeachingAllocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_teach_different_subjects_across_divisions(): void
    {
        $standard = Standard::factory()->create(['name' => 'Standard 10']);
        $divisionA = Division::factory()->for($standard)->create(['name' => 'A']);
        $divisionB = Division::factory()->for($standard)->create(['name' => 'B']);
        $divisionC = Division::factory()->for($standard)->create(['name' => 'C']);
        $english = Subject::factory()->create(['name' => 'English']);
        $science = Subject::factory()->create(['name' => 'Science']);
        $teacher = TeacherProfile::factory()->create();

        $teacher->teachingAllocations()->createMany([
            ['division_id' => $divisionA->id, 'subject_id' => $english->id],
            ['division_id' => $divisionB->id, 'subject_id' => $english->id],
            ['division_id' => $divisionC->id, 'subject_id' => $science->id],
        ]);

        $this->assertCount(3, $teacher->teachingAllocations);
        $this->assertCount(2, $english->teachingAllocations);
        $this->assertCount(1, $divisionC->teachingAllocations);
    }

    public function test_two_teachers_can_share_the_same_subject_in_a_division(): void
    {
        $division = Division::factory()->create();
        $subject = Subject::factory()->create();

        foreach (TeacherProfile::factory()->count(2)->create() as $teacher) {
            $teacher->teachingAllocations()->create([
                'division_id' => $division->id,
                'subject_id' => $subject->id,
            ]);
        }

        $this->assertCount(2, $division->teachingAllocations);
    }

    public function test_same_teacher_cannot_be_allocated_the_same_subject_and_division_twice(): void
    {
        $division = Division::factory()->create();
        $subject = Subject::factory()->create();
        $teacher = TeacherProfile::factory()->create();

        $allocation = ['division_id' => $division->id, 'subject_id' => $subject->id];

        $teacher->teachingAllocations()->create($allocation);

        $this->expectException(UniqueConstraintViolationException::class);

        $teacher->teachingAllocations()->create($allocation);
    }
}
