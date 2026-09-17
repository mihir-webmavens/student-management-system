<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\StudentProfile;
use App\Models\User;
use App\Notifications\SetPasswordNotification;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
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

    public function test_admin_can_view_the_create_user_form(): void
    {
        $this->seed(RoleSeeder::class);

        $this->actingAs(User::factory()->create()->assignRole('admin'));

        $response = $this->get(route('users.create'));

        $response->assertOk();
        $response->assertSee('Teacher');
        $response->assertDontSee('Super Admin');
    }

    public function test_non_admin_cannot_create_users(): void
    {
        $this->seed(RoleSeeder::class);

        $this->actingAs(User::factory()->create()->assignRole('teacher'));

        $this->get(route('users.create'))->assertForbidden();

        $this->post(route('users.store'), $this->newUserData(['role' => 'teacher']))->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'new.user@example.com']);
    }

    public function test_admin_can_create_a_user_with_a_role(): void
    {
        Notification::fake();

        $this->seed(RoleSeeder::class);

        $this->actingAs(User::factory()->create()->assignRole('admin'));

        $response = $this->post(route('users.store'), $this->newUserData(['role' => 'teacher']));

        $response->assertSessionHasNoErrors()->assertRedirect(route('users.index'));

        $user = User::query()->where('email', 'new.user@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole('teacher'));
        $this->assertNull($user->studentProfile);

        Notification::assertSentTo($user, SetPasswordNotification::class);
    }

    public function test_admin_cannot_set_the_new_users_password(): void
    {
        Notification::fake();

        $this->seed(RoleSeeder::class);

        $this->actingAs(User::factory()->create()->assignRole('admin'));

        $this->post(route('users.store'), $this->newUserData([
            'role' => 'teacher',
            'password' => 'chosen-by-admin',
            'password_confirmation' => 'chosen-by-admin',
        ]))->assertSessionHasNoErrors();

        $user = User::query()->where('email', 'new.user@example.com')->firstOrFail();

        $this->assertFalse(Hash::check('chosen-by-admin', $user->password));
    }

    public function test_new_user_can_set_their_password_from_the_emailed_link(): void
    {
        Notification::fake();

        $this->seed(RoleSeeder::class);

        $this->actingAs(User::factory()->create()->assignRole('admin'));

        $this->post(route('users.store'), $this->newUserData(['role' => 'teacher']));

        auth()->logout();

        $user = User::query()->where('email', 'new.user@example.com')->firstOrFail();

        $this->assertNull($user->email_verified_at);

        Notification::assertSentTo($user, SetPasswordNotification::class, function (SetPasswordNotification $notification) use ($user): bool {
            $this->get($notification->toMail($user)->actionUrl)->assertOk();

            $this->post(route('password.update'), [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'my-own-password',
                'password_confirmation' => 'my-own-password',
            ])->assertSessionHasNoErrors();

            return true;
        });

        $user->refresh();

        $this->assertTrue(Hash::check('my-own-password', $user->password));
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_admin_must_choose_a_valid_division_when_creating_a_student(): void
    {
        $this->seed(RoleSeeder::class);

        $division = Division::factory()->create();
        $otherDivision = Division::factory()->create();

        $this->actingAs(User::factory()->create()->assignRole('admin'));

        $this->post(route('users.store'), $this->newUserData(['role' => 'student']))
            ->assertSessionHasErrors(['standard_id', 'division_id']);

        $this->post(route('users.store'), $this->newUserData([
            'role' => 'student',
            'standard_id' => $division->standard_id,
            'division_id' => $otherDivision->id,
        ]))->assertSessionHasErrors(['division_id']);

        $this->post(route('users.store'), $this->newUserData([
            'role' => 'student',
            'standard_id' => $division->standard_id,
            'division_id' => $division->id,
        ]))->assertSessionHasNoErrors();

        $user = User::query()->where('email', 'new.user@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole('student'));
        $this->assertEquals($division->id, $user->studentProfile->division_id);
    }

    public function test_only_a_super_admin_can_create_a_super_admin(): void
    {
        $this->seed(RoleSeeder::class);

        $this->actingAs(User::factory()->create()->assignRole('admin'));

        $this->post(route('users.store'), $this->newUserData(['role' => 'super-admin']))
            ->assertSessionHasErrors(['role']);

        $this->actingAs(User::factory()->create()->assignRole('super-admin'));

        $this->post(route('users.store'), $this->newUserData(['role' => 'super-admin']))
            ->assertSessionHasNoErrors();

        $this->assertTrue(User::query()->where('email', 'new.user@example.com')->firstOrFail()->hasRole('super-admin'));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function newUserData(array $overrides = []): array
    {
        return [
            'name' => 'New User',
            'email' => 'new.user@example.com',
            ...$overrides,
        ];
    }
}
