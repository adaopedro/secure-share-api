<?php

namespace App\Post;

use App\Shared\File;

final class PostDTO
{
    public ?int $postId;
    readonly public string $title;
    readonly public string $description;
    readonly public string $postType;
    readonly public string $userId;
    readonly public File $coverPhoto;
    readonly public array $attachments;

    public function __construct(array $data)
    {
        $this->title = $data["title"];
        $this->description = $data["description"];
        $this->postType = $data["post_type"];
        $this->userId = $data["user_id"];
        $this->coverPhoto = $data["cover_photo"];
        $this->attachments = $data["attachments"];
    }
}
