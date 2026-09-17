<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Name -->
            <flux:input
                name="name"
                :label="__('Name')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('Full name')"
            />

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Standard & Division -->
            <div
                x-data="{
                    standards: @js($standards->map(fn ($standard) => ['id' => $standard->id, 'name' => $standard->name, 'divisions' => $standard->divisions->map->only('id', 'name')])),
                    standardId: @js((string) old('standard_id', '')),
                    divisionId: @js((string) old('division_id', '')),
                    get divisions() {
                        return this.standards.find((standard) => String(standard.id) === this.standardId)?.divisions ?? [];
                    },
                }"
                class="grid grid-cols-2 gap-4"
            >
                <flux:select name="standard_id" :label="__('Standard')" x-model="standardId" x-on:change="divisionId = ''" required>
                    <option value="">{{ __('Select standard') }}</option>
                    @foreach ($standards as $standard)
                        <option value="{{ $standard->id }}">{{ $standard->name }}</option>
                    @endforeach
                </flux:select>

                <flux:select name="division_id" :label="__('Division')" x-model="divisionId" x-bind:disabled="! standardId" required>
                    <option value="">{{ __('Select division') }}</option>
                    <template x-for="division in divisions" :key="division.id">
                        <option :value="division.id" x-text="division.name" :selected="String(division.id) === divisionId"></option>
                    </template>
                </flux:select>
            </div>

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Password')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Confirm password')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    {{ __('Create account') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
