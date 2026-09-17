<?php

namespace App\Http\Requests;

use App\Models\Division;
use App\Models\Standard;
use App\Models\TeacherProfile;
use App\Models\TeachingAllocation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeachingAllocationRequest extends FormRequest
{
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
        return [
            'teacher_profile_id' => ['required', 'integer', Rule::exists(TeacherProfile::class, 'id')->where('status', 'active')],
            'standard_id' => ['required', 'integer', Rule::exists(Standard::class, 'id')],
            'division_id' => [
                'required',
                'integer',
                Rule::exists(Division::class, 'id')->where('standard_id', $this->input('standard_id')),
            ],
            'subject_id' => [
                'required',
                'integer',
                Rule::exists('division_subject', 'subject_id')->where('division_id', $this->input('division_id')),
                Rule::unique(TeachingAllocation::class)
                    ->where('teacher_profile_id', $this->input('teacher_profile_id'))
                    ->where('division_id', $this->input('division_id')),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'subject_id.exists' => __('The selected subject is not taught in this division.'),
            'subject_id.unique' => __('This teacher already teaches this subject in this division.'),
        ];
    }
}
