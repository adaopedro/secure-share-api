<?php
    namespace App\Shared;

    final class File {

        private function __construct(
            readonly public mixed $data,
            readonly public string $mimeType,
        ) {}

        public static function fromBase64(string $base64, string $mimeType,): self {
            return new self( base64_decode($base64), $mimeType,);
        }
        
        public static function fromCache(string $key, string $mimeType,): self {
            return new self( $key, $mimeType,);
        }
       
        public static function fromBinaries(mixed $binaries, string $mimeType,): self {
            return new self( $binaries, $mimeType);
        }


    }