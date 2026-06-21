<?php
namespace App\Validations\Rules\Wallet;

class GetPaymentsByTypeRequest
{
    public static function rules(): array
    {
        return [
           'type'  => ['required', 'string'], // Payment, Backup, Withdrawal
            'page' => ['required', 'number'],
        ];
    }
}