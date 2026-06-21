<?php
namespace App\Validations\Rules\Notification;

class GetUnreadRequest
{
    public static function rules(): array
    {
        return [
            'limit' => ['number'],
            'offset'=> ['number'],
        ];
    }
}