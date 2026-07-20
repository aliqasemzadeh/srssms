<?php

namespace App\Http\Requests\Auth;

use App\Concerns\MobileValidationRules;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;

class LoginRequest extends FortifyLoginRequest
{
    use MobileValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            Fortify::username() => $this->mobileFieldRules(),
            'password' => ['required', 'string'],
            'remember' => ['sometimes'],
        ];
    }
}
