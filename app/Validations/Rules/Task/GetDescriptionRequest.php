<?php
namespace App\Validations\Rules\Task;

class GetDescriptionRequest
{
    public static function rules(): array
    {
        return [
           'id' => ['required', 'number'],
        ];
    }
}