<?php
namespace App\Validations\Rules\Task;

class UpdateTaskStatusRequest
{
    public static function rules(): array
    {
        return [
            'status' => ['required', 'string'],
            'id'     => ['required', 'number'],
        ];
    }
}