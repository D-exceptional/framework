<?php
namespace App\Validations\Rules\Mail;

class GetOutboxRequest
{
    public static function rules(): array
    {
        return [
           'page'  => ['required', 'number'],
        ];
    }
}