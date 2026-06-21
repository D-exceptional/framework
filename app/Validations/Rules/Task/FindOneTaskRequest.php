<?php
namespace App\Validations\Rules\Task;

class FindOneTaskRequest
{
    public static function rules(): array
    {
        return [
            'id' => ['required', 'number'],
        ];
    }
}