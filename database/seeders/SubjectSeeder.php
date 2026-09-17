<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Seed the common school subjects.
     */
    public function run(): void
    {
        $subjects = [
            'ENG' => 'English',
            'HIN' => 'Hindi',
            'GUJ' => 'Gujarati',
            'MATH' => 'Mathematics',
            'SCI' => 'Science',
            'SST' => 'Social Science',
            'CS' => 'Computer Science',
        ];

        foreach ($subjects as $code => $name) {
            Subject::firstOrCreate(['name' => $name], ['code' => $code]);
        }
    }
}
