<?php
namespace App\Validations\Rules\Mail;

class GetMailRequest
{
    public static function rules(): array
    {
        return [
           'id'  => ['required', 'number'],
        ];
    }
}