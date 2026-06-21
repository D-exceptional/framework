<?php
namespace App\Validations\Rules\Wallet;

class UpdatePaymentDetailsRequest
{
    public static function rules(): array
    {
        return [
           'account' => ['required', 'number'],
            'bank'   => ['required', 'string'],
            'code'   => ['required', 'string'],
        ];
    }
}