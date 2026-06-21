<?php
namespace App\Validations\Rules\Wallet;

class GetPaymentsByChannelRequest
{
    public static function rules(): array
    {
        return [
            'channel' => ['required', 'string'], 
            'status'  => ['required', 'string'], 
            'page'    => ['required', 'number'],
        ];
    }
}