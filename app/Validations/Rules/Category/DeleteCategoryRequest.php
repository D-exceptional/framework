<?php
namespace App\Validations\Rules\Category;

class DeleteCategoryRequest
{
    public static function rules(): array
    {
        return [
           'id' => ['required', 'number'],
        ];
    }
}