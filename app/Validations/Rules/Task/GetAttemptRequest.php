<?php
namespace App\Validations\Rules\Task;

class GetAttemptRequest
{
    public static function rules(): array
    {
        return [
            'id'   => ['required', 'number'],
            'page' => ['required', 'number'],
        ];
    }
}