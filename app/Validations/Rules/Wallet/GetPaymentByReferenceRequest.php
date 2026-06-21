<?php
namespace App\Validations\Rules\Wallet;

class GetPaymentByReferenceRequest
{
    public static function rules(): array
    {
        return [
           'type'       => ['required', 'string'], // Membership, Withdrawal
            'reference' => ['required', 'string'],
        ];
    }
}