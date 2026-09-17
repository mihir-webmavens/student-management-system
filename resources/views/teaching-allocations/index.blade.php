<x-layouts::app :title="__('Teaching Allocations')">
    <div class="flex w-full flex-1 flex-col gap-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <flux:heading size="xl" level="1">{{ __('Teaching Allocations') }}</flux:heading>
                <flux:subheading>{{ __('Which teacher teaches which subject in each division.') }}</flux:subheading>
            </div>

            <flux:button :href="route('teaching-allocations.create')" variant="primary" size="sm" icon="plus" wire:navigate>
                {{ __('Allocate Subject') }}
            </flux:button>
        </div>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle" :heading="session('status')" />
        @endif

        <form
            method="GET"
            action="{{ route('teaching-allocations.index') }}"
            x-data="{
                standards: @js($standards->map(fn ($standard) => ['id' => $standard->id, 'divisions' => $standard->divisions->map->only('id', 'name')])),
                standardId: @js((string) ($filters['standard_id'] ?? '')),
                divisionId: @js((string) ($filters['division_id'] ?? '')),
                get divisions() {
                    return this.standards.find((standard) => String(standard.id) === this.standardId)?.divisions ?? [];
                },
                submit() {
                    this.$nextTick(() => this.$root.requestSubmit());
                },
            }"
            class="grid gap-4 rounded-xl border border-zinc-200 p-4 sm:grid-cols-2 lg:grid-cols-5 dark:border-zinc-700"
        >
            <div class="sm:col-span-2 lg:col-span-2">
                <flux:input
                    name="search"
                    :label="__('Teacher')"
                    :value="$filters['search']"
                    icon="magnifying-glass"
                    :placeholder="__('Name, email or employee code')"
                    clearable
                />
            </div>

            <flux:select name="standard_id" :label="__('Standard')" x-model="standardId" x-on:change="divisionId = ''; submit()">
                <option value="">{{ __('All standards') }}</option>
                @foreach ($standards as $standard)
                    <option value="{{ $standard->id }}">{{ $standard->name }}</option>
                @endforeach
            </flux:select>

            <flux:select name="division_id" :label="__('Division')" x-model="divisionId" x-on:change="submit()" x-bind:disabled="! standardId">
                <option value="">{{ __('All divisions') }}</option>
                <template x-for="division in divisions" :key="division.id">
                    <option :value="division.id" x-text="division.name" :selected="String(division.id) === divisionId"></option>
                </template>
            </flux:select>

            <flux:select name="subject_id" :label="__('Subject')" x-on:change="submit()">
                <option value="">{{ __('All subjects') }}</option>
                @foreach ($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected($filters['subject_id'] === $subject->id)>{{ $subject->name }}</option>
                @endforeach
            </flux:select>

            <div class="flex items-center justify-between gap-2 sm:col-span-2 lg:col-span-5">
                <flux:text>
                    {{ trans_choice(':count allocation found|:count allocations found', $allocations->total(), ['count' => $allocations->total()]) }}
                </flux:text>

                <div class="flex items-center gap-2">
                    @if (array_filter($filters))
                        <flux:button :href="route('teaching-allocations.index')" variant="ghost" size="sm" icon="x-mark" wire:navigate>
                            {{ __('Reset') }}
                        </flux:button>
                    @endif

                    <flux:button type="submit" variant="primary" size="sm" icon="funnel">{{ __('Filter') }}</flux:button>
                </div>
            </div>
        </form>

        <flux:table :paginate="$allocations">
            <flux:table.columns>
                <flux:table.column>{{ __('Teacher') }}</flux:table.column>
                <flux:table.column>{{ __('Employee Code') }}</flux:table.column>
                <flux:table.column>{{ __('Standard / Division') }}</flux:table.column>
                <flux:table.column>{{ __('Subject') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($allocations as $allocation)
                    <flux:table.row :key="$allocation->id">
                        <flux:table.cell class="flex items-center gap-3">
                            <flux:avatar size="xs" :initials="$allocation->teacherProfile->user->initials()" />
                            {{ $allocation->teacherProfile->user->name }}
                        </flux:table.cell>

                        <flux:table.cell>{{ $allocation->teacherProfile->employee_code }}</flux:table.cell>

                        <flux:table.cell>{{ $allocation->division->standard->name }} - {{ $allocation->division->name }}</flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm">{{ $allocation->subject->name }}</flux:badge>
                        </flux:table.cell>

                        <flux:table.cell align="end">
                            <form method="POST" action="{{ route('teaching-allocations.destroy', $allocation) }}" onsubmit="return confirm(@js(__('Remove this teaching allocation?')))">
                                @csrf
                                @method('DELETE')

                                <flux:button type="submit" variant="ghost" size="sm" icon="trash" :aria-label="__('Remove')" />
                            </form>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5">{{ array_filter($filters) ? __('No teaching allocations match these filters.') : __('No teaching allocations yet.') }}</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</x-layouts::app>
