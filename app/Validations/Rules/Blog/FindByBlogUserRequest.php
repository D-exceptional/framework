<?php
namespace App\Validations\Rules\Blog;

class FindByBlogUserRequest
{
    public static function rules(): array
    {
        return [
            'id'   => ['required', 'number'],
            'page' => ['required', 'number'],
        ];
    }
}