<?php
    namespace App\User\Exceptions;

    use App\Shared\Exceptions\BadRequestException;

    final class NipIdAlreadyExistsException extends BadRequestException {
        public function __construct() {
            parent::__construct("Este NIP ja esta em uso");
        }
    }