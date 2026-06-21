<?php
namespace App\Validations\Rules\Category;

class UpdateCategoryRequest
{
    public static function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'id'   => ['required', 'number'],
        ];
    }
}