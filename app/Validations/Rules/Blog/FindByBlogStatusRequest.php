<?php
namespace App\Validations\Rules\Blog;

class FindByBlogStatusRequest
{
    public static function rules(): array
    {
        return [
           'status' => ['required', 'string'],
           'page'   => ['required', 'number'],
        ];
    }
}