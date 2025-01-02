<?php

namespace App\PostAttachment;

use function React\Async\await;

final class PostAttachmentService
{

    public function __construct(private PostAttachmentRepository $postAttachmentRepository) {}

    public function add( string $name, string $postId ): int {

        return await(
            $this->postAttachmentRepository->addAsync($name, $postId)
        );
    }
   
    public function updateFilename( int $postAttachmentId, string $filename ): void {

        await(
            $this->postAttachmentRepository->updateFilenameAsync($postAttachmentId, $filename)
        );
    }

    public function getAllByPostId(int $postId): array
    {
        return await(
            $this->postAttachmentRepository->getAllByPostIdAsync($postId)
        );
    }
}
