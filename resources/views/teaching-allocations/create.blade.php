<x-layouts::app :title="__('Allocate Subject')">
    <div class="flex w-full max-w-2xl flex-col gap-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <flux:heading size="xl" level="1">{{ __('Allocate Subject') }}</flux:heading>
                <flux:subheading>{{ __('Assign a teacher to teach a subject in a division.') }}</flux:subheading>
            </div>

            <flux:button :href="route('teaching-allocations.index')" variant="ghost" size="sm" icon="arrow-left" wire:navigate>
                {{ __('Back') }}
            </flux:button>
        </div>

        <form
            method="POST"
            action="{{ route('teaching-allocations.store') }}"
            x-data="{
                standards: @js($standards->map(fn ($standard) => [
                    'id' => $standard->id,
                    'divisions' => $standard->divisions->map(fn ($division) => [
                        'id' => $division->id,
                        'name' => $division->name,
                        'subjects' => $division->subjects->map->only('id', 'name'),
                    ]),
                ])),
                standardId: @js((string) old('standard_id', '')),
                divisionId: @js((string) old('division_id', '')),
                subjectId: @js((string) old('subject_id', '')),
                get divisions() {
                    return this.standards.find((standard) => String(standard.id) === this.standardId)?.divisions ?? [];
                },
                get subjects() {
                    return this.divisions.find((division) => String(division.id) === this.divisionId)?.subjects ?? [];
                },
            }"
            class="flex flex-col gap-6"
        >
            @csrf

            <flux:select name="teacher_profile_id" :label="__('Teacher')" required>
                <option value="">{{ __('Select teacher') }}</option>
                @foreach ($teachers as $teacher)
                    <option value="{{ $teacher->id }}" @selected((string) old('teacher_profile_id') === (string) $teacher->id)>
                        {{ $teacher->user->name }} ({{ $teacher->employee_code }}){{ $teacher->specialization ? ' - '.$teacher->specialization : '' }}
                    </option>
                @endforeach
            </flux:select>

            <div class="grid gap-4 sm:grid-cols-3">
                <flux:select name="standard_id" :label="__('Standard')" x-model="standardId" x-on:change="divisionId = ''; subjectId = ''" required>
                    <option value="">{{ __('Select standard') }}</option>
                    @foreach ($standards as $standard)
                        <option value="{{ $standard->id }}">{{ $standard->name }}</option>
                    @endforeach
                </flux:select>

                <flux:select name="division_id" :label="__('Division')" x-model="divisionId" x-on:change="subjectId = ''" x-bind:disabled="! standardId" required>
                    <option value="">{{ __('Select division') }}</option>
                    <template x-for="division in divisions" :key="division.id">
                        <option :value="division.id" x-text="division.name" :selected="String(division.id) === divisionId"></option>
                    </template>
                </flux:select>

                <flux:select name="subject_id" :label="__('Subject')" x-model="subjectId" x-bind:disabled="! divisionId" required>
                    <option value="">{{ __('Select subject') }}</option>
                    <template x-for="subject in subjects" :key="subject.id">
                        <option :value="subject.id" x-text="subject.name" :selected="String(subject.id) === subjectId"></option>
                    </template>
                </flux:select>
            </div>

            <flux:text x-show="divisionId && subjects.length === 0" style="display: none">
                {{ __('No subjects are linked to this division yet.') }}
            </flux:text>

            <div class="flex items-center justify-end gap-2">
                <flux:button :href="route('teaching-allocations.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
                <flux:button type="submit" variant="primary" data-test="allocate-subject-button">{{ __('Allocate') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
