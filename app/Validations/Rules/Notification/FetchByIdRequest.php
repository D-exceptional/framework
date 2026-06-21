<?php
namespace App\Validations\Rules\Notification;

class FetchByIdRequest
{
    public static function rules(): array
    {
        return [
            'limit'  => ['number'],
            'offset' => ['number'],
        ];
    }
}