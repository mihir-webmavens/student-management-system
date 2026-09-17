<?php

namespace Tests\Feature\Settings;

use App\Livewire\Settings\Profile;
use App\Models\Division;
use App\Models\StudentProfile;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $this->actingAs($user = User::factory()->create());

        $this->get('/settings/profile')->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = Livewire::test(Profile::class)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->call('updateProfileInformation');

        $response->assertHasNoErrors();

        $user->refresh();

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = Livewire::test(Profile::class)
            ->set('name', 'Test User')
            ->set('email', $user->email)
            ->call('updateProfileInformation');

        $response->assertHasNoErrors();

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_student_can_update_their_standard_and_division(): void
    {
        $this->seed(RoleSeeder::class);

        $profile = StudentProfile::factory()->create();
        $user = $profile->user->assignRole('student');
        $newDivision = Division::factory()->create();

        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->assertSet('standard_id', $profile->division->standard_id)
            ->assertSet('division_id', $profile->division_id)
            ->set('standard_id', $newDivision->standard_id)
            ->assertSet('division_id', null)
            ->set('division_id', $newDivision->id)
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        $this->assertEquals($newDivision->id, $user->studentProfile()->first()->division_id);
    }

    public function test_student_without_a_profile_must_choose_a_valid_division(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create()->assignRole('student');
        $division = Division::factory()->create();
        $otherDivision = Division::factory()->create();

        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->call('updateProfileInformation')
            ->assertHasErrors(['standard_id' => 'required', 'division_id' => 'required'])
            ->set('standard_id', $division->standard_id)
            ->set('division_id', $otherDivision->id)
            ->call('updateProfileInformation')
            ->assertHasErrors(['division_id'])
            ->set('division_id', $division->id)
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        $this->assertEquals($division->id, $user->studentProfile()->first()->division_id);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = Livewire::test('settings.delete-user-form')
            ->set('password', 'password')
            ->call('deleteUser');

        $response
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertNull($user->fresh());
        $this->assertFalse(auth()->check());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = Livewire::test('settings.delete-user-form')
            ->set('password', 'wrong-password')
            ->call('deleteUser');

        $response->assertHasErrors(['password']);

        $this->assertNotNull($user->fresh());
    }
}
