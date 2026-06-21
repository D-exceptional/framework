<?php
namespace App\Validations\Rules\User;

class StatusRequest
{
    public static function rules(): array
    {
        return [
            'status' => ['required', 'string'],
            'id'     => ['required', 'number'],
        ];
    }
}