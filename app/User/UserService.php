<?php

namespace App\User;

use Mimey\MimeTypes;
use Random\Randomizer;
use App\Shared\DocumentService;
use function React\Async\await;
use Clue\React\Redis\Client as RedisClient;
use App\User\Exceptions\UserNotExistsException;
use App\User\Exceptions\EmailAlreadyExistsException;
use App\User\Exceptions\NipIdAlreadyExistsException;
use App\Shared\File;
use App\User\Exceptions\InvalidUserDataException;

final class UserService
{

    private array $keysToDeleteFromCacheOnFinish;

    public function __construct(
        private UserRepository $userRepository,
        private DocumentService $documentService,
        private RedisClient $redisClient,
        private UserValidator $userValidator,
        private string $uploadsDirectory,
    ) {}

    public function create(array &$data): int
    {

        if (!array_key_exists("profile_picture", $data)) {
            throw new InvalidUserDataException("A Profile picture as a JSON object (with base64 and mime_type keys) is required");
        }

        $key = new Randomizer()->nextInt();
        await(
            $this->redisClient->set($key, base64_decode($data["profile_picture"]->base64))
        );
        $this->keysToDeleteFromCacheOnFinish[] = $key;

        $mimeType = $data["profile_picture"]->mime_type;

        unset($data["profile_picture"]);

        $data["profile_picture"] = File::fromCache(
            key: $key,
            mimeType: $mimeType
        );

        try {
            $this->userValidator->validate($data, ["password_reset_code"]);
        } catch (\Throwable $e) {
            $this->deleteCachedData();
            throw new InvalidUserDataException($e->getMessage());
        }

        $dto = new UserDTO($data);

        if ($this->checkIfUserExistsByEmail($dto->email)) {
            $this->deleteCachedData();
            throw new EmailAlreadyExistsException;
        }

        if ($this->checkIfUserExistsByNipId($dto->nipId)) {
            $this->deleteCachedData();
            throw new NipIdAlreadyExistsException;
        }

        $encryptedPassword = password_hash($dto->password, PASSWORD_DEFAULT);

        $dto->userId = await(
            $this->userRepository->addAsync(
                $dto->firstName,
                $dto->lastName,
                $dto->nipId,
                $dto->contact,
                $dto->email,
                $encryptedPassword,
                $dto->isAdmin
            )
        );

        $extension = new MimeTypes()->getExtension($dto->profilePictureFile->mimeType);
        $filename = md5($dto->userId) . "." . $extension;
        $fullPath = $this->uploadsDirectory . DIRECTORY_SEPARATOR . "users" . DIRECTORY_SEPARATOR . $filename;

        $this->documentService->uploadDocument(
            $fullPath,
            $dto->profilePictureFile->data,
        );

        $this->userRepository->updateProfilePictureAsync($dto->userId, $filename);

        $this->deleteCachedData();

        return $dto->userId;
    }

    public function updatePassword(int $userId, array $data): void
    {

        if (!$this->checkIfUserExistsById($userId)) {
            throw new UserNotExistsException;
        }

        $keysToSkipOnDataValidation = ["first_name", "last_name",  "nip_id", "contact", "email", "is_admin", "profile_picture"];

        try {
            $this->userValidator->validate(
                data: $data,
                except: $keysToSkipOnDataValidation
            );
        } catch (\Throwable $e) {
            throw new InvalidUserDataException($e->getMessage());
        }

        // checkar o request code atraves do userId ==> userId && request_code && is_used && lifetime (timestamp)

        $encryptedPassword = $this->generatePasswordHash($data["password"]);

        await(
            $this->userRepository->updatePasswordAsync($userId, $encryptedPassword)
        );
    }

    public function getAll(bool $withSensitiveData = false): array
    {

        return array_map(
            array: await($this->userRepository->getAllAsync()),
            callback: function ($item) use ($withSensitiveData) {
                if (!$withSensitiveData) {
                    unset($item["password"]);
                }

                return $item;
            }
        );
    }

    public function getById(int $userId, bool $withSensitiveData = false): array
    {
        $user = await(
            $this->userRepository->getByIdAsync($userId)
        );

        if (!(bool) $user) {
            throw new UserNotExistsException;
        }

        if (!$withSensitiveData) {
            unset($user["password"]);
        }

        return $user;
    }

    public function getByEmail(string $email, bool $withSensitiveData = false): array
    {
        $user = await(
            $this->userRepository->getByEmailAsync($email)
        );

        if (!(bool) $user) {
            throw new UserNotExistsException;
        }

        if (!$withSensitiveData) {
            unset($user["password"]);
        }

        return $user;
    }

    public function getTotalOfUsers(): int
    {
        return await(
            $this->userRepository->getTotalOfUsers()
        );
    }

    public function checkIfUserExistsById(string $userId): bool
    {
        return (bool) await(
            $this->userRepository->getByIdAsync($userId)
        );
    }

    private function checkIfUserExistsByNipId(string $nipId): bool
    {
        return (bool) await(
            $this->userRepository->getByNipIdAsync($nipId)
        );
    }

    public function checkIfUserExistsByEmail(string $email): bool
    {
        return (bool) await(
            $this->userRepository->getByEmailAsync($email)
        );
    }

    private function generatePasswordHash(string $value): string
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }

    private function deleteCachedData(): void
    {
        $this->redisClient->__call("DEL", $this->keysToDeleteFromCacheOnFinish);
    }
}
