<?php
namespace App\Validations\Rules\Blog;

class CreateBlogRequest
{
    public static function rules(): array
    {
        return [
            'banner'   => ['required', 'string'],
            'title'    => ['required', 'string'],
            'category' => ['required', 'string'],
            'article'  => ['required', 'string'],
        ];
    }
}