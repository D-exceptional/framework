<?php
namespace App\Validations\Rules\Wallet;

class GetPaymentSummaryRequest
{
    public static function rules(): array
    {        
        return [
           'view'    => ['required', 'string'],
            'period' => ['required', 'string'],
            'start'  => ['required', 'string'],
            'end'    => ['required', 'string'],
        ];
    }
}