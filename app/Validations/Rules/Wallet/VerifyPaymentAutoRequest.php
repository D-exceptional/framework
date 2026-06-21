<?php
namespace App\Validations\Rules\Wallet;

class VerifyPaymentAutoRequest
{
    public static function rules(): array
    {
        return [
           'id'         => ['required', 'number'],
            'reference' => ['required', 'string'],
        ];
    }
}