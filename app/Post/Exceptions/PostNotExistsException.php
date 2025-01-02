<?php
    namespace App\Post\Exceptions;

    use App\Shared\Exceptions\BadRequestException;

    final class PostNotExistsException extends BadRequestException {
        public function __construct() {
            parent::__construct("Publicacao nao encontrada");
        }
    }