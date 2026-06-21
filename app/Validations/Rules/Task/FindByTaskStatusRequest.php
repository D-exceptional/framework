<?php
namespace App\Validations\Rules\Task;

class FindByTaskStatusRequest
{
    public static function rules(): array
    {
        return [
            'status' => ['required', 'string'],
            'page'   => ['required', 'number'],
        ];
    }
}