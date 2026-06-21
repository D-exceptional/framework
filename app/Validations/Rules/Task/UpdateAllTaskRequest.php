<?php
namespace App\Validations\Rules\Task;

class UpdateAllTaskRequest
{
    public static function rules(): array
    {
        return [
            'status' => ['required', 'string'],
        ];
    }
}