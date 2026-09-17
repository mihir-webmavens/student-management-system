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
                        <flux:table.cell colspan="5">{{ __('No teaching allocations yet.') }}</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</x-layouts::app>
