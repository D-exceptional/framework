<?php
namespace App\Validations\Rules\Wallet;

class CreatePaymentRequest
{
    public static function rules(): array
    {
        return [
            'amount'        => ['required', 'number'],
            'channel'       => ['required', 'string'],
            'facilitatorId' => ['required', 'number'],
            'identifier'    => ['required', 'string'],
            'narration'     => ['required', 'string'],
            'receipt'       => ['required', 'string'],
        ];
    }
}