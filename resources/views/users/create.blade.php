<x-layouts::app :title="__('Create User')">
    <div class="flex w-full max-w-2xl flex-col gap-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <flux:heading size="xl" level="1">{{ __('Create User') }}</flux:heading>
                <flux:subheading>{{ __('Add a new user and choose their role.') }}</flux:subheading>
            </div>

            <flux:button :href="route('users.index')" variant="ghost" size="sm" icon="arrow-left" wire:navigate>
                {{ __('Back') }}
            </flux:button>
        </div>

        <form
            method="POST"
            action="{{ route('users.store') }}"
            x-data="{
                role: @js((string) old('role', '')),
                standards: @js($standards->map(fn ($standard) => ['id' => $standard->id, 'divisions' => $standard->divisions->map->only('id', 'name')])),
                standardId: @js((string) old('standard_id', '')),
                divisionId: @js((string) old('division_id', '')),
                get divisions() {
                    return this.standards.find((standard) => String(standard.id) === this.standardId)?.divisions ?? [];
                },
            }"
            class="flex flex-col gap-6"
        >
            @csrf

            <flux:input
                name="name"
                :label="__('Name')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="off"
                :placeholder="__('Full name')"
            />

            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autocomplete="off"
                placeholder="email@example.com"
            />

            <flux:select name="role" :label="__('Role')" x-model="role" required>
                <option value="">{{ __('Select role') }}</option>
                @foreach ($roles as $role)
                    <option value="{{ $role }}">{{ str($role)->headline() }}</option>
                @endforeach
            </flux:select>

            <div x-show="role === 'student'" @style(['display: none' => old('role') !== 'student']) class="grid grid-cols-2 gap-4">
                <flux:select name="standard_id" :label="__('Standard')" x-model="standardId" x-on:change="divisionId = ''" x-bind:required="role === 'student'" x-bind:disabled="role !== 'student'">
                    <option value="">{{ __('Select standard') }}</option>
                    @foreach ($standards as $standard)
                        <option value="{{ $standard->id }}">{{ $standard->name }}</option>
                    @endforeach
                </flux:select>

                <flux:select name="division_id" :label="__('Division')" x-model="divisionId" x-bind:required="role === 'student'" x-bind:disabled="role !== 'student' || ! standardId">
                    <option value="">{{ __('Select division') }}</option>
                    <template x-for="division in divisions" :key="division.id">
                        <option :value="division.id" x-text="division.name" :selected="String(division.id) === divisionId"></option>
                    </template>
                </flux:select>
            </div>

            <flux:callout icon="envelope" variant="secondary" :text="__('The user will receive an email with a link to set their own password.')" />

            <div class="flex items-center justify-end gap-2">
                <flux:button :href="route('users.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
                <flux:button type="submit" variant="primary" data-test="create-user-button">{{ __('Create User') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
