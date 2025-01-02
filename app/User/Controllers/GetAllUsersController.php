<?php

namespace App\User\Controllers;

use App\User\UserService;
use App\Shared\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

final class GetAllUsersController
{

    public function __construct( private UserService $userService ) {}

    public function __invoke(ServerRequestInterface $request )
    {
        try {
            return JsonResponse::ok(
                $this->userService->getAll()
            );
        } catch (\Throwable $e) {
            echo "Error: " . $e->getTraceAsString() . PHP_EOL;

            return JsonResponse::internalServerError($e->getMessage());
        }
    }
}
