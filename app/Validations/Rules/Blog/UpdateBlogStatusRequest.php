<?php
namespace App\Validations\Rules\Blog;

class UpdateBlogStatusRequest
{
    public static function rules(): array
    {
        return [
           'status' => ['required', 'string'],
           'id'     => ['required', 'number'],
        ];
    }
}