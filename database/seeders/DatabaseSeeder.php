<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([RoleSeeder::class, StandardSeeder::class, SubjectSeeder::class]);

        $user = User::factory()->create([
            'name' => 'Mihir Soni',
            'email' => 'mihir@webmavens.com',
        ]);

        $user->assignRole('student');

        $user->studentProfile()->create([
            'user_id' => $user->id,
            'division_id' => 45,
        ]);
    }
}
