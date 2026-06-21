<?php
namespace App\Validations\Rules\Task;

class LoadTaskRequest
{
    public static function rules(): array
    {
        return [
            'page' => ['required', 'number'],
        ];
    }
}