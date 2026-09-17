<?php

namespace Tests\Feature;

use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('users.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_see_users_with_their_roles(): void
    {
        Role::create(['name' => 'teacher']);

        $teacher = User::factory()->create();
        $teacher->assignRole('teacher');

        $this->actingAs(User::factory()->create());

        $response = $this->get(route('users.index'));

        $response->assertOk();
        $response->assertSee($teacher->name);
        $response->assertSee($teacher->email);
        $response->assertSee('Teacher');
    }

    public function test_student_standard_and_division_are_shown(): void
    {
        $profile = StudentProfile::factory()->create();

        $this->actingAs(User::factory()->create());

        $response = $this->get(route('users.index'));

        $response->assertOk();
        $response->assertSee($profile->division->standard->name.' - '.$profile->division->name);
    }
}
