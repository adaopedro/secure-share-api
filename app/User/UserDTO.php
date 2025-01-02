<?php

namespace App\User;

use App\Shared\File;

final class UserDTO
{
    public ?int $userId;
    readonly public string $firstName;
    readonly public string $lastName;
    readonly public string $nipId;
    readonly public string $contact;
    readonly public string $email;
    readonly public string $password;
    readonly public bool $isAdmin;
    readonly public File $profilePictureFile;

    public function __construct(array $data)
    {
        $this->firstName = $data["first_name"];
        $this->lastName = $data["last_name"];
        $this->nipId = $data["nip_id"];
        $this->contact = $data["contact"];
        $this->email = $data["email"];
        $this->password = $data["password"];
        $this->isAdmin = $data["is_admin"];
        $this->profilePictureFile = $data["profile_picture"];
    }
}
