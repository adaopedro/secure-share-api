<?php
    namespace App\Shared\Exceptions;

    final class RequestedFileNotFoundException extends BadRequestException {
        public function __construct() {
            parent::__construct("Ficheiro nao encontrado");
        }
    }