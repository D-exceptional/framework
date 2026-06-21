<?php
namespace App\Validations\Rules\User;

class SubscribeRequest
{
    public static function rules(): array
    {
        return [
            'token'     => ['required', 'string'],
            'device_id' => ['required', 'string'],
        ];
    }
}