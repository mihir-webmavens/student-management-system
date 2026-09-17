<?php

namespace App\Concerns;

use App\Models\Division;
use App\Models\Standard;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait ProfileValidationRules
{
    /**
     * Get the validation rules used to validate user profiles.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function profileRules(?int $userId = null): array
    {
        return [
            'name' => $this->nameRules(),
            'email' => $this->emailRules($userId),
        ];
    }

    /**
     * Get the validation rules used to validate user names.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function nameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Get the validation rules used to validate user emails.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function emailRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }

    /**
     * Get the validation rules used to validate a student's standard and division.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function studentProfileRules(mixed $standardId): array
    {
        return [
            'standard_id' => ['required', 'integer', Rule::exists(Standard::class, 'id')],
            'division_id' => [
                'required',
                'integer',
                Rule::exists(Division::class, 'id')->where('standard_id', $standardId),
            ],
        ];
    }
}
