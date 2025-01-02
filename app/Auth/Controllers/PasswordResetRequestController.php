<?php

namespace App\Auth\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use App\Auth\AuthService;
use App\Shared\JsonResponse;
use App\Shared\Exceptions\BadRequestException;

final class PasswordResetRequestController
{

    public function __construct(private AuthService $authService) {}

    public function __invoke(ServerRequestInterface $request)
    {

        $data = $request->getParsedBody();
        $email = $data["email"] ?? "";

        try {
            $result = $this->authService->requestPasswordResetCode($email);

            return JsonResponse::ok([
                "message" => "success",
                "password_reset_code" => $result
            ]);
        } catch (BadRequestException $e) {
            return JsonResponse::badRequest([$e->getMessage()]);
        } catch (\Throwable $e) {
            echo "Error: " . $e->getTraceAsString() . PHP_EOL;

            return JsonResponse::internalServerError($e->getMessage());
        }
    }
}
