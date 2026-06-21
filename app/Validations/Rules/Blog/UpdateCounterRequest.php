<?php
namespace App\Validations\Rules\Blog;

class UpdateCounterRequest
{
    public static function rules(): array
    {
        return [
           'id'   => ['required', 'number'],
           'type' => ['required', 'string'],
        ];
    }
}