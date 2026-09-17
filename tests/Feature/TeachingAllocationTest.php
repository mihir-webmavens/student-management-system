<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\Standard;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\TeachingAllocation;
use App\Models\User;
use Database\Seeders\DivisionSubjectSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\StandardSeeder;
use Database\Seeders\SubjectSeeder;
use Database\Seeders\TeacherSeeder;
use Database\Seeders\TeachingAllocationSeeder;
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

    public function test_non_admins_cannot_manage_teaching_allocations(): void
    {
        $this->seed(RoleSeeder::class);

        $allocation = TeachingAllocation::factory()->create();

        $this->actingAs(User::factory()->create()->assignRole('teacher'));

        $this->get(route('teaching-allocations.index'))->assertForbidden();
        $this->get(route('teaching-allocations.create'))->assertForbidden();
        $this->post(route('teaching-allocations.store'), [])->assertForbidden();
        $this->delete(route('teaching-allocations.destroy', $allocation))->assertForbidden();

        $this->assertModelExists($allocation);
    }

    public function test_admin_can_view_allocations_and_the_create_form(): void
    {
        $this->seed(RoleSeeder::class);

        $allocation = TeachingAllocation::factory()->create();
        $teacher = TeacherProfile::factory()->create();
        $teacher->user->assignRole('teacher');

        $this->actingAs(User::factory()->create()->assignRole('admin'));

        $this->get(route('teaching-allocations.index'))
            ->assertOk()
            ->assertSee($allocation->teacherProfile->user->name)
            ->assertSee($allocation->subject->name);

        $this->get(route('teaching-allocations.create'))
            ->assertOk()
            ->assertSee($teacher->employee_code);
    }

    public function test_admin_can_allocate_a_subject_of_the_division_to_a_teacher(): void
    {
        $this->seed(RoleSeeder::class);

        $division = Division::factory()->create();
        $subject = Subject::factory()->create();
        $division->subjects()->attach($subject);
        $teacher = TeacherProfile::factory()->create();

        $this->actingAs(User::factory()->create()->assignRole('admin'));

        $this->post(route('teaching-allocations.store'), [
            'teacher_profile_id' => $teacher->id,
            'standard_id' => $division->standard_id,
            'division_id' => $division->id,
            'subject_id' => $subject->id,
        ])->assertSessionHasNoErrors()->assertRedirect(route('teaching-allocations.index'));

        $this->assertDatabaseHas('teaching_allocations', [
            'teacher_profile_id' => $teacher->id,
            'division_id' => $division->id,
            'subject_id' => $subject->id,
        ]);
    }

    public function test_allocation_is_rejected_for_invalid_combinations(): void
    {
        $this->seed(RoleSeeder::class);

        $division = Division::factory()->create();
        $otherDivision = Division::factory()->create();
        $subject = Subject::factory()->create();
        $subjectNotInDivision = Subject::factory()->create();
        $division->subjects()->attach($subject);
        $teacher = TeacherProfile::factory()->create();
        $inactiveTeacher = TeacherProfile::factory()->inactive()->create();

        TeachingAllocation::factory()->for($teacher)->for($division)->for($subject)->create();

        $this->actingAs(User::factory()->create()->assignRole('admin'));

        $data = [
            'teacher_profile_id' => $teacher->id,
            'standard_id' => $division->standard_id,
            'division_id' => $division->id,
            'subject_id' => $subject->id,
        ];

        $this->post(route('teaching-allocations.store'), $data)
            ->assertSessionHasErrors(['subject_id' => 'This teacher already teaches this subject in this division.']);

        $this->post(route('teaching-allocations.store'), [...$data, 'subject_id' => $subjectNotInDivision->id])
            ->assertSessionHasErrors(['subject_id' => 'The selected subject is not taught in this division.']);

        $this->post(route('teaching-allocations.store'), [...$data, 'division_id' => $otherDivision->id])
            ->assertSessionHasErrors(['division_id']);

        $this->post(route('teaching-allocations.store'), [...$data, 'teacher_profile_id' => $inactiveTeacher->id])
            ->assertSessionHasErrors(['teacher_profile_id']);

        $this->assertDatabaseCount('teaching_allocations', 1);
    }

    public function test_admin_can_remove_an_allocation(): void
    {
        $this->seed(RoleSeeder::class);

        $allocation = TeachingAllocation::factory()->create();

        $this->actingAs(User::factory()->create()->assignRole('admin'));

        $this->delete(route('teaching-allocations.destroy', $allocation))
            ->assertRedirect(route('teaching-allocations.index'));

        $this->assertModelMissing($allocation);
    }

    public function test_seeders_allocate_a_matching_teacher_to_every_division_subject(): void
    {
        $this->seed([
            RoleSeeder::class,
            StandardSeeder::class,
            SubjectSeeder::class,
            DivisionSubjectSeeder::class,
            TeacherSeeder::class,
            TeachingAllocationSeeder::class,
        ]);

        $this->assertSame(13, TeacherProfile::query()->count());
        $this->assertSame(13, User::role('teacher')->count());
        $this->assertSame(Division::query()->withCount('subjects')->get()->sum('subjects_count'), TeachingAllocation::query()->count());

        TeachingAllocation::query()->with(['teacherProfile', 'subject'])->get()->each(
            fn (TeachingAllocation $allocation) => $this->assertSame($allocation->subject->name, $allocation->teacherProfile->specialization),
        );

        $this->seed([TeacherSeeder::class, TeachingAllocationSeeder::class]);

        $this->assertSame(13, TeacherProfile::query()->count());
        $this->assertSame(Division::query()->withCount('subjects')->get()->sum('subjects_count'), TeachingAllocation::query()->count());
    }
}
