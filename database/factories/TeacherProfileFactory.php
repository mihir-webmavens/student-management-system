<?php

namespace Database\Factories;

use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeacherProfile>
 */
class TeacherProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'employee_code' => 'EMP-'.fake()->unique()->numerify('#####'),
            'phone' => fake()->phoneNumber(),
            'date_of_birth' => fake()->dateTimeBetween('-60 years', '-22 years'),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'address' => fake()->address(),
            'joining_date' => fake()->dateTimeBetween('-10 years'),
            'qualification' => fake()->randomElement(['B.Ed', 'M.Ed', 'M.Sc', 'M.A', 'Ph.D']),
            'specialization' => fake()->randomElement(['Mathematics', 'Science', 'English', 'History', 'Computer Science']),
            'status' => 'active',
        ];
    }

    /**
     * Indicate that the teacher is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
