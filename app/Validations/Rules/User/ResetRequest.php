<?php
namespace App\Validations\Rules\User;

class ResetRequest
{
    public static function rules(): array
    {
        return [
            'email'    => ['required', 'string'],
            'password' => ['required', 'string'],
            'otp'      => ['required', 'number'],
        ];
    }
}