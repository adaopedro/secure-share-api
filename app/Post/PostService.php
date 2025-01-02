<?php

namespace App\Post;

use App\Post\Exceptions\InvalidPostDataException;
use Mimey\MimeTypes;
use App\Post\Exceptions\PostNotExistsException;
use App\PostAttachment\PostAttachmentDTO;
use App\PostAttachment\PostAttachmentService;
use App\Shared\DocumentService;
use App\User\Exceptions\UserNotExistsException;
use App\User\UserService;
use Random\Randomizer;
use Clue\React\Redis\Client as RedisClient;
use App\Shared\File;
use stdClass;

use function React\Async\await;

final class PostService
{
    private array $keysToDeleteFromCacheOnFinish;

    public function __construct(
        private PostRepository $postRepository,
        private PostAttachmentService $postAttachmentService,
        private DocumentService $documentService,
        private RedisClient $redisClient,
        private UserService $userService,
        private PostValidator $postValidator,
        private string $uploadsDirectory
    ) {}

    public function publish(array $data): int
    {

        if (!array_key_exists("cover_photo", $data)) {
            throw new InvalidPostDataException("A Cover photo as a JSON object (with base64 and mime_type keys) is required");
        }

        $key = new Randomizer()->nextInt();
        await(
            $this->redisClient->set($key, base64_decode($data["cover_photo"]->base64))
        );
        $this->keysToDeleteFromCacheOnFinish[] = $key;

        $mimeType = $data["cover_photo"]->mime_type;

        unset($data["cover_photo"]);

        $data["cover_photo"] = File::fromCache(
            key: $key,
            mimeType: $mimeType
        );

        if (array_key_exists("attachments", $data)) {
            $data["attachments"] = array_map(
                array: (array) $data["attachments"],
                callback: function (stdClass $attachment) {

                    $key = new Randomizer()->nextInt();
                    await(
                        $this->redisClient->set($key, base64_decode($attachment->base64))
                    );
                    $this->keysToDeleteFromCacheOnFinish[] = $key;

                    $mimeType = $attachment->mime_type;
                    $name = $attachment->name;

                    unset($attachment);

                    return new PostAttachmentDTO(
                        $name,
                        File::fromCache(
                            key: $key,
                            mimeType: $mimeType
                        )
                    );
                }
            );
        }

        try {
            $this->postValidator->validate($data, ["attachments"]);
        } catch (\Throwable $e) {
            $this->deleteCachedData();

            throw new InvalidPostDataException($e->getMessage());
        }

        $dto = new PostDTO($data);

        if (!$this->userService->checkIfUserExistsById($dto->userId)) {
            $this->deleteCachedData();

            throw new UserNotExistsException;
        }

        $dto->postId = await(
            $this->postRepository->addAsync(
                $dto->title,
                $dto->description,
                $dto->postType,
                $dto->userId
            )
        );

        $extension = new MimeTypes()->getExtension($dto->coverPhoto->mimeType);
        $filename = md5($dto->postId) . "." . $extension;
        $fullPath = $this->uploadsDirectory . DIRECTORY_SEPARATOR . "posts" . DIRECTORY_SEPARATOR . $filename;

        $this->documentService->uploadDocument(
            $fullPath,
            $dto->coverPhoto->data,
        );

        $this->postRepository->updateCoverPhotoAsync($dto->postId, $filename);

        if (count($dto->attachments) > 0) {
            $this->addAttachments($dto);
        }

        $this->deleteCachedData();

        return $dto->postId;
    }

    public function getAll(?int $userId = null, ?string $postType = null): array
    {
        if ($userId && $postType) {
            $posts = await(
                $this->postRepository->getAllByUserIdAndPostTypeAsync($userId, $postType)
            );

            return $this->attachPostAttachmentsToPostCollection($posts);
        }

        if ($postType) {
            $posts = await(
                $this->postRepository->getAllByPostTypeAsync($postType)
            );

            return $this->attachPostAttachmentsToPostCollection($posts);
        }

        if ($userId) {
            $posts = await(
                $this->postRepository->getAllByUserIdAsync($userId)
            );

            return $this->attachPostAttachmentsToPostCollection($posts);
        }

        $posts = await(
            $this->postRepository->getAllAsync()
        );
        return $this->attachPostAttachmentsToPostCollection($posts);
    }

    public function getById(int $postId): array
    {
        $post = await($this->postRepository->getByIdAsync($postId));

        if (!$post) {
            throw new PostNotExistsException;
        }

        $post["attachments"] = $this->postAttachmentService->getAllByPostId($postId);

        return $post;
    }

    public function addViewToPost(int $postId): void
    {
        if (!$this->checkIfPostExistsById($postId)) {
            throw new PostNotExistsException;
        }

        await(
            $this->postRepository->addViewToPostAsync($postId)
        );
    }

    public function getTotalOfPosts(): int
    {
        return await($this->postRepository->getTotalOfPosts());
    }

    public function getTotalOfPostsByType(): array
    {
        return await($this->postRepository->getTotalOfPostsByType());
    }

    public function getRecentPosts(int $max): array
    {
        return await($this->postRepository->getRecentPosts($max));
    }

    private function attachPostAttachmentsToPostCollection(array $posts): array
    {

        $receivedPosts = $posts;

        foreach ($receivedPosts as &$post) {
            $post["attachments"] = $this->postAttachmentService->getAllByPostId($post["id"]);
        }

        return $receivedPosts;
    }

    private function checkIfPostExistsById(string $postId): bool
    {
        return (bool) await(
            $this->postRepository->getByIdAsync($postId)
        );
    }

    private function addAttachments(PostDTO $dto): void
    {

        /** @var PostAttachmentDTO $attachment */
        foreach ($dto->attachments as $attachment) {
            $attachmentId = $this->postAttachmentService->add(
                $attachment->name,
                $dto->postId
            );

            $extension = new MimeTypes()->getExtension($attachment->file->mimeType);
            $filename = md5($attachmentId) . "." . $extension;
            $fullPath = $this->uploadsDirectory . DIRECTORY_SEPARATOR . "posts_attachments" . DIRECTORY_SEPARATOR . $filename;

            $this->documentService->uploadDocument(
                filename: $fullPath,
                data: $attachment->file->data
            );

            $this->postAttachmentService->updateFilename($attachmentId, $filename);
        }
    }

    private function deleteCachedData(): void
    {
        $this->redisClient->__call("DEL", $this->keysToDeleteFromCacheOnFinish);
    }
}
