<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\Subject;
use Database\Seeders\DivisionSubjectSeeder;
use Database\Seeders\StandardSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DivisionSubjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_divisions_and_subjects_have_a_many_to_many_relationship(): void
    {
        [$divisionA, $divisionB] = Division::factory()->count(2)->create();
        [$english, $science] = Subject::factory()->count(2)->create();

        $divisionA->subjects()->attach([$english->id, $science->id]);
        $divisionB->subjects()->attach($english);

        $this->assertEqualsCanonicalizing([$english->id, $science->id], $divisionA->subjects->pluck('id')->all());
        $this->assertEqualsCanonicalizing([$divisionA->id, $divisionB->id], $english->divisions->pluck('id')->all());
        $this->assertNotNull($divisionA->subjects->first()->pivot->created_at);
    }

    public function test_same_subject_cannot_be_attached_to_a_division_twice(): void
    {
        $division = Division::factory()->create();
        $subject = Subject::factory()->create();

        $division->subjects()->attach($subject);

        $this->expectException(UniqueConstraintViolationException::class);

        $division->subjects()->attach($subject);
    }

    public function test_seeder_attaches_subjects_to_divisions_by_standard(): void
    {
        $this->seed([StandardSeeder::class, SubjectSeeder::class, DivisionSubjectSeeder::class]);

        $firstStandardDivision = Division::query()->whereRelation('standard', 'name', 'Standard 1')->firstOrFail();
        $sixthStandardDivision = Division::query()->whereRelation('standard', 'name', 'Standard 6')->firstOrFail();
        $tenthStandardDivision = Division::query()->whereRelation('standard', 'name', 'Standard 10')->firstOrFail();

        $this->assertEqualsCanonicalizing(['ENG', 'HIN', 'GUJ', 'MATH'], $firstStandardDivision->subjects->pluck('code')->all());
        $this->assertEqualsCanonicalizing(['ENG', 'HIN', 'GUJ', 'MATH', 'SCI', 'SST'], $sixthStandardDivision->subjects->pluck('code')->all());
        $this->assertEqualsCanonicalizing(['ENG', 'HIN', 'GUJ', 'MATH', 'SCI', 'SST', 'CS'], $tenthStandardDivision->subjects->pluck('code')->all());

        $this->seed(DivisionSubjectSeeder::class);

        $this->assertCount(7, $tenthStandardDivision->subjects()->get());
    }
}
