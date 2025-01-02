<?php
    namespace App\Auth\Exceptions;

    use App\Shared\Exceptions\BadRequestException;

    final class InvalidEmailOrPasswordException extends BadRequestException {
        public function __construct(string $errorMessage = "Credenciais invalidas") {
            parent::__construct($errorMessage);
        }
    }