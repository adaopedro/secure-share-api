<?php
    namespace App\User\Exceptions;

    use App\Shared\Exceptions\BadRequestException;

    final class InvalidUserDataException extends BadRequestException {
        public function __construct(string $errorMessage) {
            parent::__construct($errorMessage);
        }
    }