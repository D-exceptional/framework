<?php
namespace App\Validations\Rules\Category;

class CreateCategoryRequest
{
    public static function rules(): array
    {
        return [
           'category' => ['required', 'string'],
        ];
    }
}