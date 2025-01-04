<?php

namespace App\Auth;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Firebase\JWT\JWT;
use Random\Randomizer;
use App\Auth\Exceptions\InvalidEmailOrPasswordException;
use App\User\UserService;

final class AuthService
{

    public function __construct(private UserService $userService) {}

    public function login(string $email, string $password): array
    {

        $user = $this->userService->getByEmail($email, true);

        if (!$this->verifyPassword($password, $user["password"])) {
            throw new InvalidEmailOrPasswordException;
        }

        unset($user["password"]);

        return [
            "status" => "Success",
            "message" => "Login bem sucesso",
            "user_data" => $user,
            "access_token" => [
                "token" => $this->generateJwt($user["id"]),
                "token_type" => "Bearer",
            ],
        ];
    }

    public function requestPasswordResetCode(string $email): array
    {
        $user = $this->userService->getByEmail($email);

        if (!(bool) $user) {
            throw new InvalidEmailOrPasswordException("Email nao encontrado");
        }

        $randomValue = (string) (new Randomizer())->nextInt();
        $passwordResetCode = (int) substr($randomValue, 0, 4);

        // salvar o reset code! ==> user_id, reset_code, expires_at (timestamp), is_used created_at

        return [
            "status" => "Success",
            "message" => "Pedido de recuperacao da password aceite",
            "user_id" => $user["id"],
            "password_reset_code" => $passwordResetCode
        ];

    }

    private function verifyPassword(string $informedPassword, string $savedPassword): bool
    {
        return password_verify($informedPassword, $savedPassword);
    }

    private function generateJwt(int $userId): string
    {
        $issuedAt = new DateTimeImmutable("now", new DateTimeZone("africa/luanda"));
        $expiresAt = $issuedAt->add(new DateInterval("P10D"));
        $key = "secureshare";

        $payload = [
            "iss" => "https://secureshare.ao",
            "iat" => $issuedAt->getTimestamp(),
            "exp" => $expiresAt->getTimestamp(),
            "data" => ["user_id" => $userId]
        ];

        return JWT::encode($payload, $key, "HS256");
    }
}
