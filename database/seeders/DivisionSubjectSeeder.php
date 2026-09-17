<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class DivisionSubjectSeeder extends Seeder
{
    /**
     * Attach subjects to every division based on its standard.
     *
     * Standards 1-4 study languages and Mathematics, standards 5-8 add Science and Social Science,
     * and standards 9-12 also take Computer Science.
     */
    public function run(): void
    {
        $subjectIds = Subject::query()->pluck('id', 'code');

        $divisions = Division::query()->with('standard')->get();

        foreach ($divisions as $division) {
            $codes = $this->subjectCodesFor($division->standard->sort_order);

            $division->subjects()->syncWithoutDetaching($subjectIds->only($codes)->values()->all());
        }
    }

    /**
     * Get the subject codes taught in the given standard.
     *
     * @return array<int, string>
     */
    private function subjectCodesFor(int $standardNumber): array
    {
        $codes = ['ENG', 'HIN', 'GUJ', 'MATH'];

        if ($standardNumber >= 5) {
            $codes = [...$codes, 'SCI', 'SST'];
        }

        if ($standardNumber >= 9) {
            $codes[] = 'CS';
        }

        return $codes;
    }
}
