<?php

namespace App\Http\Requests\Auth;

use App\Concerns\MobileValidationRules;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\SendPasswordResetLinkRequest as FortifySendPasswordResetLinkRequest;

class SendPasswordResetLinkRequest extends FortifySendPasswordResetLinkRequest
{
    use MobileValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            Fortify::email() => $this->mobileFieldRules(),
        ];
    }
}
