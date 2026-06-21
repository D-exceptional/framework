<?php
namespace App\Validations\Rules\Wallet;

class GetPaymentsByUserRequest
{
    public static function rules(): array
    {
        return [
           'type'  => ['required', 'string'], // database table name
            'page' => ['required', 'number'],
        ];
    }
}