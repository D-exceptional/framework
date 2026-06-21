<?php
namespace App\Validations\Rules\User;

class RegisterRequest
{
    public static function rules(): array
    {
        return [
           'avatar'       => ['required', 'string'],
            'firstname'   => ['required', 'string'],
            'lastname'    => ['required', 'string'],
            'email'       => ['required', 'email'], 
            'contact'     => ['required', 'string', 'min:7'],
            'country'     => ['required', 'string'],
            'password'    => ['required', 'string', 'min:6'], 
            'membership'  => ['required', 'string'], 
            'code'        => ['required', 'string'],
            'currency'    => ['required', 'string'],
            'narration'   => ['required', 'string'], // Manual/Auto
            'facilitator' => ['required', 'string'], // Admin/Leader
            'id'          => ['required', 'number'], 
            'amount'      => ['required', 'number'], 
            'receipt'     => ['required', 'string'], 
            'state'       => ['required', 'string'], // Optional
        ];
    }
}