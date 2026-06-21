<?php
namespace App\Validations\Rules\Wallet;

class GetPayoutsByStatusRequest
{
    public static function rules(): array
    {
        return [
           'status' => ['required', 'string'], 
            'page'   => ['required', 'number'],
        ];
    } 
}