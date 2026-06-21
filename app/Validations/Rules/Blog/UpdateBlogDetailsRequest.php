<?php
namespace App\Validations\Rules\Blog;

class UpdateBlogDetailsRequest
{
    public static function rules(): array
    {
        return [
            'title'   => ['required', 'string'],
            'article' => ['required', 'string'],
            'id'      => ['required', 'number'],
        ];
    }
}