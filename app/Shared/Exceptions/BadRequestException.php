<?php
    namespace App\Shared\Exceptions;

    class BadRequestException extends \Exception {
        public function __construct(string $message) {
            parent::__construct($message);
        }
    }