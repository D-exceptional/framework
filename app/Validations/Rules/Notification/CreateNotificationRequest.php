<?php
namespace App\Validations\Rules\Notification;

class CreateNotificationRequest
{
    public static function rules(): array
    {
        return [
            'details'   => ['required', 'string'],
            'type'      => ['required', 'string'],
            'receiver'  => ['required', 'int'],
            'date'      => ['required', 'string'],
            'status'    => ['required', 'string']
        ];
    }
}