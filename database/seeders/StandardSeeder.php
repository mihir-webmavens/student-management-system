<?php

namespace Database\Seeders;

use App\Models\Standard;
use Illuminate\Database\Seeder;

class StandardSeeder extends Seeder
{
    /**
     * Seed standards 1 to 12, each with divisions A to D.
     */
    public function run(): void
    {
        foreach (range(1, 12) as $number) {
            $standard = Standard::firstOrCreate(
                ['name' => "Standard {$number}"],
                ['sort_order' => $number],
            );

            foreach (['A', 'B', 'C', 'D'] as $divisionName) {
                $standard->divisions()->firstOrCreate(['name' => $divisionName]);
            }
        }
    }
}
