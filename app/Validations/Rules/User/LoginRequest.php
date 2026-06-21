<?php
namespace App\Validations\Rules\User;

class LoginRequest
{
    public static function rules(): array
    {
        return [
            'email'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }
}