<?php
namespace App\Validations\Rules\Wallet;

class SingleTransferRequest
{
    public static function rules(): array
    {
        return [
           'bank'      => ['required', 'string'], 
            'account'   => ['required', 'string'],
            'amount'    => ['required', 'number'],
            'narration' => ['required', 'string'],
            'currency'  => ['required', 'string'], 
            'reference' => ['required', 'string'],
        ];
    }
}