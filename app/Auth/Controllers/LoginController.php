<?php

namespace App\Auth\Controllers;

use App\Auth\AuthService;
use Psr\Http\Message\ServerRequestInterface;
use App\Shared\JsonResponse;
use App\Shared\Exceptions\BadRequestException;

final class LoginController
{

    public function __construct(private AuthService $authService) {}

    public function __invoke(ServerRequestInterface $request)
    {

        $data = $request->getParsedBody();

        $email = $data["email"] ?? "";
        $password = $data["password"] ?? "";

        try {
            $data = $this->authService->login($email, $password);

            return JsonResponse::ok($data);
            
        } catch (BadRequestException $e) {
            return JsonResponse::badRequest([$e->getMessage()]);
        } catch (\Throwable $e) {
            echo "Error: " . $e->getTraceAsString() . PHP_EOL;

            return JsonResponse::internalServerError($e->getMessage());
        }
    }
}
