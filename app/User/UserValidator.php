<?php

namespace App\User;

use App\Shared\Validator;

final class UserValidator extends Validator
{

    protected array $rules = [
        "first_name" => "required|min:2|max:15",
        "last_name" => "required|min:2|max:15",
        "nip_id" => "required|min:2|max:10|numeric",
        "contact" => "required|min:9|max:20",
        "email" => "required|email",
        "password" => "required|min:8",
        "is_admin" => "required|numeric",
        "profile_picture" => "required",
        "password_reset_code" => "required|numeric|min:4|max:4",
    ];

    public function validate(array $data, array $except = []): true
    {
        return parent::validate($data, $except);
    }
}
