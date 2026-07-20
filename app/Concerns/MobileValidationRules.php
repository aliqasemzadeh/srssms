<?php

namespace App\Concerns;

use App\Models\User;
use Illuminate\Validation\Rule;

trait MobileValidationRules
{
    /**
     * Get the validation rules used to validate mobile numbers.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function mobileRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'ir_mobile:zero',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }

    /**
     * Get the validation rules used to validate mobile number fields without uniqueness.
     *
     * @return array<int, string>
     */
    protected function mobileFieldRules(): array
    {
        return ['required', 'string', 'ir_mobile:zero'];
    }
}
