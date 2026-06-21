<?php
namespace App\Validations\Rules\User;

class UpdateRequest
{
    public static function rules(): array
    {
        return [
            'bio' => ['required', 'string'],
        ];
    }
}