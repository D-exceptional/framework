<?php
namespace App\Validations\Rules\Task;

class DeleteTaskRequest
{
    public static function rules(): array
    {
        return [
            'id' => ['required', 'number'],
        ];
    }
}