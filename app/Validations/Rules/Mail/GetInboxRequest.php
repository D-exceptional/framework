<?php
namespace App\Validations\Rules\Mail;

class GetInboxRequest
{
    public static function rules(): array
    {
        return [
           'page'  => ['required', 'number'],
        ];
    }
}