<?php
namespace App\Validations\Rules\Blog;

class DeleteBlogRequest
{
    public static function rules(): array
    {
        return [
           'id' => ['required', 'number'],
        ];
    }
}