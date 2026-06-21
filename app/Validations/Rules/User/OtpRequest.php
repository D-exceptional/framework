<?php
namespace App\Validations\Rules\User;

class OtpRequest
{
    public static function rules(): array
    {
        return [
            'email' => ['required', 'string'],
        ];
    }
}