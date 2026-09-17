<x-layouts::app :title="__('Subjects')">
    <div class="flex w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl" level="1">{{ $isStudent ? __('My Subjects') : __('Subjects') }}</flux:heading>

            @if ($isStudent && $division)
                <flux:subheading>{{ __('Subjects taught in :standard - :division.', ['standard' => $division->standard->name, 'division' => $division->name]) }}</flux:subheading>
            @elseif (! $isStudent)
                <flux:subheading>{{ __('All subjects and the number of divisions they are taught in.') }}</flux:subheading>
            @endif
        </div>

        @if ($isStudent && ! $division)
            <flux:callout variant="warning" icon="exclamation-triangle" :heading="__('You have not been assigned a standard and division yet.')" :text="__('Please contact the school office to have your division assigned.')" />
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Subject') }}</flux:table.column>
                    <flux:table.column>{{ __('Code') }}</flux:table.column>
                    <flux:table.column>{{ $isStudent ? __('Teacher') : __('Divisions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($subjects as $subject)
                        <flux:table.row :key="$subject->id">
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:icon.book-open variant="mini" class="text-zinc-400" />
                                {{ $subject->name }}
                            </flux:table.cell>

                            <flux:table.cell>
                                @if ($subject->code)
                                    <flux:badge size="sm">{{ $subject->code }}</flux:badge>
                                @else
                                    <flux:text>&mdash;</flux:text>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell>
                                @if ($isStudent)
                                    @forelse ($subject->teachingAllocations as $allocation)
                                        <div>{{ $allocation->teacherProfile->user->name }}</div>
                                    @empty
                                        <flux:text>{{ __('Not assigned yet') }}</flux:text>
                                    @endforelse
                                @else
                                    {{ $subject->divisions_count }}
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="3">{{ __('No subjects found.') }}</flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</x-layouts::app>
