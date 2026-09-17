<?php

namespace App\Http\Requests;

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    use ProfileValidationRules;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['super-admin', 'admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            ...$this->profileRules(),
            'role' => ['required', 'string', Rule::in($this->user()->assignableRoleNames())],
        ];

        if ($this->input('role') === 'student') {
            $rules = [...$rules, ...$this->studentProfileRules($this->input('standard_id'))];
        }

        return $rules;
    }
}
