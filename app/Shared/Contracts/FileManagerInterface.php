<?php

namespace App\Shared\Contracts;

interface FileManagerInterface
{
    public function upload(string $filename, mixed $data): string;
    public function delete(string $filename): bool;
    public function createDirectory(string $path): bool;
    public function deleteDirectory(string $path): bool;
}
