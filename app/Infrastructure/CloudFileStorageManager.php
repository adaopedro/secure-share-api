<?php

namespace App\Infrastructure;

use App\Shared\Contracts\FileManagerInterface;

final class CloudFileStorageManager implements FileManagerInterface
{

    public function upload(string $filename, mixed $data): string
    {
        return "";
    }

    public function delete(string $filename): bool
    {
        return true;
    }

    public function createDirectory(string $path): bool
    {
        return true;
    }

    public function deleteDirectory(string $path): bool
    {
        return true;
    }


}
