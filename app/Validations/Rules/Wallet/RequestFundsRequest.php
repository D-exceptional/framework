<?php
namespace App\Validations\Rules\Wallet;

class RequestFundsRequest
{
    public static function rules(): array
    {
        return [
           'amount'       => ['required', 'number'],
            'narration'   => ['required', 'string'],
            'description' => ['required', 'string'], // Admin Withdrawal, Task Withdrawal, Referral Withdrawal, Bonus Withdrawal
        ];
    }
}