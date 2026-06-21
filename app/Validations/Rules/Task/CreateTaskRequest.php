<?php
namespace App\Validations\Rules\Task;

class CreateTaskRequest
{
    public static function rules(): array
    {
        return [
            'name'        => ['required', 'string'],
            'description' => ['required', 'string'],
            'reward'      => ['required', 'number'],
            'start'       => ['required', 'string'],
            'end'         => ['required', 'string'],
        ];
    }
}