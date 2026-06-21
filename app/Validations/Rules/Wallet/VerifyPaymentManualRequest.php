<?php
namespace App\Validations\Rules\Wallet;

class VerifyPaymentManualRequest
{
    public static function rules(): array
    {
        return [
           'reference' => ['required', 'string'],
        ];
    }
}