<?php
namespace App\Validations\Rules\User;

class PasswordRequest
{
    public static function rules(): array
    {
        return [
            'password'    => ['required', 'string'],
            'newpassword' => ['required', 'string'],
        ];
    }
}