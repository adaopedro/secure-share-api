<?php

namespace App\PostAttachment;

use App\Shared\File;

readonly final class PostAttachmentDTO
{
    public function __construct(
        public string $name,
        public File $file, 
    ){}
}

