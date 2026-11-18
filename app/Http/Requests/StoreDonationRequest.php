<?php

namespace App\Http\Requests;

use App\Enums\DonationFund;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDonationRequest extends FormRequest
{
    /**
     * Preset gift amounts in BDT, shown as quick picks on the donation form.
     */
    public const array PRESET_AMOUNTS = [1000, 5000, 10000, 25000];

    public const int MINIMUM_AMOUNT = 10;

    public const int MAXIMUM_AMOUNT = 500000;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'fund' => ['required', Rule::enum(DonationFund::class)],
            'amount' => ['required', Rule::in([...array_map('strval', self::PRESET_AMOUNTS), 'other'])],
            'other_amount' => ['required_if:amount,other', 'nullable', 'integer', 'min:'.self::MINIMUM_AMOUNT, 'max:'.self::MAXIMUM_AMOUNT],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'other_amount.required_if' => 'Enter the amount you would like to give.',
        ];
    }

    /**
     * The chosen gift in whole BDT: a preset, or the amount typed under "Other".
     */
    public function donationAmount(): int
    {
        return $this->input('amount') === 'other'
            ? $this->integer('other_amount')
            : $this->integer('amount');
    }
}
