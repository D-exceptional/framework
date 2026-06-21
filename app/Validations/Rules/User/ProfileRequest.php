<?php
namespace App\Validations\Rules\User;

class ProfileRequest
{
    public static function rules(): array
    {
        return [
            'avatar' => ['required', 'string'],
        ];
    }
}