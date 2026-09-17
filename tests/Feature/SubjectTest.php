<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeachingAllocation;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('subjects.index'))->assertRedirect(route('login'));
    }

    public function test_student_sees_only_the_subjects_of_their_division_with_teachers(): void
    {
        $division = Division::factory()->create();
        $otherDivision = Division::factory()->create();

        $english = Subject::factory()->create(['name' => 'English']);
        $science = Subject::factory()->create(['name' => 'Science']);
        $history = Subject::factory()->create(['name' => 'History']);

        $division->subjects()->attach([$english->id, $science->id]);
        $otherDivision->subjects()->attach([$english->id, $history->id]);

        $englishTeacher = TeachingAllocation::factory()->for($division)->for($english)->create();
        $otherDivisionTeacher = TeachingAllocation::factory()->for($otherDivision)->for($english)->create();

        $student = StudentProfile::factory()->for($division)->create()->user->assignRole('student');

        $response = $this->actingAs($student)->get(route('subjects.index'));

        $response->assertOk();
        $response->assertSee('My Subjects');
        $response->assertSee($division->standard->name.' - '.$division->name);
        $response->assertSee('English');
        $response->assertSee('Science');
        $response->assertDontSee('History');
        $response->assertSee($englishTeacher->teacherProfile->user->name);
        $response->assertDontSee($otherDivisionTeacher->teacherProfile->user->name);
        $response->assertSee('Not assigned yet');
    }

    public function test_student_without_a_division_sees_a_notice(): void
    {
        Subject::factory()->create(['name' => 'English']);

        $student = User::factory()->create()->assignRole('student');

        $response = $this->actingAs($student)->get(route('subjects.index'));

        $response->assertOk();
        $response->assertSee('You have not been assigned a standard and division yet.');
        $response->assertDontSee('English');
    }

    public function test_admin_sees_all_subjects(): void
    {
        [$divisionA, $divisionB] = Division::factory()->count(2)->create();
        $english = Subject::factory()->create(['name' => 'English']);
        Subject::factory()->create(['name' => 'History']);

        $english->divisions()->attach([$divisionA->id, $divisionB->id]);

        $admin = User::factory()->create()->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('subjects.index'));

        $response->assertOk();
        $response->assertSee('English');
        $response->assertSee('History');
        $response->assertDontSee('My Subjects');
    }
}
