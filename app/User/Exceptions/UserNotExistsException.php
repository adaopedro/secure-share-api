<?php
    namespace App\User\Exceptions;

    use App\Shared\Exceptions\BadRequestException;

    final class UserNotExistsException extends BadRequestException {
        public function __construct() {
            parent::__construct("Usuario nao encontrado");
        }
    }