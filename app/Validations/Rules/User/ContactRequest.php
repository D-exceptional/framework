<?php
namespace App\Validations\Rules\User;

class ContactRequest
{
    public static function rules(): array
    {
        return [
            'name'     => ['required', 'string'],
            'email'    => ['required', 'email'],
            'contact'  => ['required', 'string'],
            'country'  => ['required', 'string'],
            'subject'  => ['required', 'string'],
            'message'  => ['required', 'string'],
            'code'     => ['required', 'string'],
        ];
    }
}