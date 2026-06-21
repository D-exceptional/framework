<?php
namespace App\Validations\Rules\Mail;

class SubscribeMailRequest
{
    public static function rules(): array
    {
        return [
           'email' => ['required', 'string'],
        ];
    }
}