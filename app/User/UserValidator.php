<?php

namespace App\User;

use App\Shared\Validator;

final class UserValidator extends Validator
{

    protected array $rules = [
        "first_name" => "required|string|min:2|max:15",
        "last_name" => "required|string|min:2|max:15",
        "nip_id" => "required|digits_between:2,10",
        "contact" => "required|string|min:9|max:20",
        "email" => "required|email",
        "password" => "required|string|min:8",
        "is_admin" => "required|numeric",
        "profile_picture" => "required",
        "password_reset_code" => "required|digits:4",
    ];

    public function validate(array $data, array $except = []): true
    {
        return parent::validate($data, $except);
    }
}
