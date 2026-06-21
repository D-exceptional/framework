<?php
namespace App\Validations\Rules\Wallet;

class GetPaymentsByStatusRequest
{
    public static function rules(): array
    {
        return [
           'table'   => ['required', 'string'], // Payment, Backup, Withdrawal
            'status' => ['required', 'string'], 
            'page'   => ['required', 'number'],
        ];
    }
}