<?php

namespace App\Shared;

use App\Shared\Contracts\FileManagerInterface;

final class DocumentService
{

    public function __construct(readonly public FileManagerInterface $manager) {}

    public function uploadDocument(string $filename, mixed $data): string
    {
        return  $this->manager->upload($filename, $data);
    }
}
