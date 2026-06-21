<?php
namespace App\Validations\Rules\User;

class SwitchRequest
{
    public static function rules(): array
    {
        return [
            'email' => ['required', 'string'],
            'role'  => ['required', 'string'],
        ];
    }
}