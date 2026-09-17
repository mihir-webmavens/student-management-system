<?php

namespace Database\Seeders;

use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    /**
     * The number of fake teachers to create for each specialization.
     *
     * @var array<string, int>
     */
    private const TEACHERS_PER_SPECIALIZATION = [
        'English' => 2,
        'Hindi' => 2,
        'Gujarati' => 2,
        'Mathematics' => 2,
        'Science' => 2,
        'Social Science' => 2,
        'Computer Science' => 1,
    ];

    /**
     * Seed fake teacher users with teacher profiles.
     *
     * Emails and employee codes are fixed (teacher1@example.com, EMP-001), so running it again does not create duplicates.
     */
    public function run(): void
    {
        $number = 0;

        foreach (self::TEACHERS_PER_SPECIALIZATION as $specialization => $count) {
            for ($i = 0; $i < $count; $i++) {
                $number++;

                $email = "teacher{$number}@example.com";

                $user = User::query()->where('email', $email)->first()
                    ?? User::factory()->create(['email' => $email]);

                $user->assignRole('teacher');

                if ($user->teacherProfile()->doesntExist()) {
                    TeacherProfile::factory()->for($user)->create([
                        'employee_code' => sprintf('EMP-%03d', $number),
                        'specialization' => $specialization,
                    ]);
                }
            }
        }
    }
}
