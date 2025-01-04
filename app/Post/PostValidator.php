<?php

namespace App\Post;

use App\Shared\Validator;

final class PostValidator extends Validator
{

    protected array $rules = [
        "title" => "required|string|min:5|max:100",
        "description" => "required|string|min:5",
        "post_type" => "required|in:Denúncia,Notícia",
        "user_id" => "required|numeric",
        "cover_photo" => "required",
        "attachments" => "array",
    ];

    public function validate(array $data, array $except = []): true
    {
        return parent::validate($data, $except);
    }

}
