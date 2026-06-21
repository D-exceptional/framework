<?php
namespace App\Validations\Rules\User;

class FetchRequest
{
    public static function rules(): array
    {
        return [
            'role' => ['required', 'string'],
            'page' => ['required', 'number']
        ];
    }
}