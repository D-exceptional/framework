<?php
namespace App\Validations\Rules\Task;

class UpdateTaskDetailsRequest
{
    public static function rules(): array
    {
        return [
            'id'          => ['required', 'number'],
            'name'        => ['required', 'string'],
            'description' => ['required', 'string'],
            'reward'      => ['required', 'number'],
            'start'       => ['required', 'string'],
            'end'         => ['required', 'string'],
            'status'      => ['required', 'string'],
        ];
    }
}