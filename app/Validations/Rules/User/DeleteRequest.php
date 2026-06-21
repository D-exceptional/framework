<?php
namespace App\Validations\Rules\User;

class DeleteRequest
{
    public static function rules(): array
    {
        return [
            'id' => ['required', 'number'],
        ];
    }
}