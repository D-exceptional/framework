<?php
namespace App\Validations\Rules\Task;

class FinalizeAttemptRequest
{
    public static function rules(): array
    {
        return [
            'id'     => ['required', 'number'],
            'user'   => ['required', 'number'],
            'status' => ['required', 'string'],
        ];
    }
}