<?php

namespace App\User\Exceptions;

use App\Shared\Exceptions\BadRequestException;

final class EmailAlreadyExistsException extends BadRequestException
{
    public function __construct()
    {
        parent::__construct("Este email ja esta em uso");
    }
}
