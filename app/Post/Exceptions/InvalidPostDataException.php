<?php
    namespace App\Post\Exceptions;

    use App\Shared\Exceptions\BadRequestException;

    final class InvalidPostDataException extends BadRequestException {
        public function __construct(string $errorMessage) {
            parent::__construct($errorMessage);
        }
    }