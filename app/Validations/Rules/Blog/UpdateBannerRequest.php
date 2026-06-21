<?php
namespace App\Validations\Rules\Blog;

class UpdateBannerRequest
{
    public static function rules(): array
    {
        return [
           'id'  => ['required', 'number'],
            'url' => ['required', 'string'],
        ];
    }
}