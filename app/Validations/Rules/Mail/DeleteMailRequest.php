<?php
namespace App\Validations\Rules\Mail;

class DeleteMailRequest
{
    public static function rules(): array
    {
        return [
           'id'  => ['required', 'number'],
        ];
    }
}