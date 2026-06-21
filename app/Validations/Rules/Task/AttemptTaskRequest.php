<?php
namespace App\Validations\Rules\Task;

class AttemptTaskRequest
{
    public static function rules(): array
    {
        return [
            'link' => ['required', 'string'],
            'id'   => ['required', 'number'],
        ];
    }
}