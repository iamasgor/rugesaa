<?php

namespace App\Http\Requests;

use App\Enums\Sector;
use App\Enums\Specialization;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->profile !== null;
    }

    /**
     * Batch, student ID, degree and name are verified by the committee, so members cannot edit them here.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:32', 'regex:/^\+?[0-9\s\-]{8,20}$/'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'remove_photo' => ['boolean'],
            'position' => ['nullable', 'string', 'max:255'],
            'organization' => ['nullable', 'string', 'max:255'],
            'sector' => ['required', Rule::enum(Sector::class)],
            'location' => ['nullable', 'string', 'max:255'],
            'specialization' => ['required', Rule::enum(Specialization::class)],
            'hall' => ['nullable', 'string', 'max:255'],
            'show_in_directory' => ['boolean'],
            'mentor_opt_in' => ['boolean'],
            'newsletter_opt_in' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a valid mobile number, for example +8801712345678.',
        ];
    }
}
