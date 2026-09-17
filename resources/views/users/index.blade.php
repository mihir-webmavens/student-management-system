<x-layouts::app :title="__('Users')">
    <div class="flex w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl" level="1">{{ __('Users') }}</flux:heading>
            <flux:subheading>{{ __('All users and their assigned roles.') }}</flux:subheading>
        </div>

        <flux:table :paginate="$users">
            <flux:table.columns>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column>{{ __('Email') }}</flux:table.column>
                <flux:table.column>{{ __('Roles') }}</flux:table.column>
                <flux:table.column>{{ __('Standard / Division') }}</flux:table.column>
                <flux:table.column>{{ __('Joined') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($users as $user)
                    <flux:table.row :key="$user->id">
                        <flux:table.cell class="flex items-center gap-3">
                            <flux:avatar size="xs" :initials="$user->initials()" />
                            {{ $user->name }}
                        </flux:table.cell>

                        <flux:table.cell>{{ $user->email }}</flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-wrap gap-1">
                                @forelse ($user->roles as $role)
                                    <flux:badge size="sm">{{ str($role->name)->headline() }}</flux:badge>
                                @empty
                                    <flux:text>{{ __('No role') }}</flux:text>
                                @endforelse
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($division = $user->studentProfile?->division)
                                {{ $division->standard->name }} - {{ $division->name }}
                            @else
                                <flux:text>&mdash;</flux:text>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>{{ $user->created_at?->format('M d, Y') }}</flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5">{{ __('No users found.') }}</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</x-layouts::app>
