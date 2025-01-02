<?php

namespace App\User\Controllers;

use App\User\UserService;
use App\Shared\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use App\Shared\Exceptions\BadRequestException;

final class GetUserByIdController
{

    public function __construct( private UserService $userService ) {}

    public function __invoke(ServerRequestInterface $request)
    {

        $userId = $request->pathParams["id"];

        try {
            $user = $this->userService->getById($userId);

            return JsonResponse::ok($user);

        } catch (BadRequestException $e) {
            return JsonResponse::badRequest([$e->getMessage()]);
        } catch (\Throwable $e) {
            echo "Error: " . $e->getTraceAsString() . PHP_EOL;

            return JsonResponse::internalServerError($e->getMessage());
        }
    }
}
