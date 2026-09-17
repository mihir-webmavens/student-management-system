<?php

namespace Tests\Feature\Auth;

use App\Models\Division;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::registration());

        $this->seed(RoleSeeder::class);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
    }

    public function test_new_users_can_register(): void
    {
        $division = Division::factory()->create();

        $response = $this->post(route('register.store'), [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'standard_id' => $division->standard_id,
            'division_id' => $division->id,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();

        $user = User::query()->where('email', 'test@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole('student'));
        $this->assertEquals($division->id, $user->studentProfile->division_id);
    }

    public function test_standard_and_division_are_required_to_register(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['standard_id', 'division_id']);

        $this->assertGuest();
    }

    public function test_division_must_belong_to_the_selected_standard(): void
    {
        $division = Division::factory()->create();
        $otherDivision = Division::factory()->create();

        $response = $this->post(route('register.store'), [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'standard_id' => $division->standard_id,
            'division_id' => $otherDivision->id,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['division_id']);

        $this->assertGuest();
    }
}
