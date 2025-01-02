<?php

namespace App\Shared;

use App\User\Exceptions\InvalidUserDataException;
use Rakit\Validation\Validator as RakitValidator;

class Validator
{

    protected array $rules = [];

    protected function validate(array $data, array $except = []): true
    {

        $rules = array_filter(
            array: $this->rules,
            callback: fn($key) => !in_array($key, $except),
            mode: ARRAY_FILTER_USE_KEY
        );

        $validator = new RakitValidator();
        $validation = $validator->make($data, $rules);
        $validation->validate();

        if ($validation->fails()) {
            $errors = $validation->errors();
            throw new InvalidUserDataException(implode("#", $errors->firstOfAll()));
        }

        return true;
    }
}
