<?php
namespace App\Validations\Rules\Blog;

class FindOneBlogRequest
{
    public static function rules(): array
    {
        return [
           'id' => ['required', 'number'],
        ];
    }
}