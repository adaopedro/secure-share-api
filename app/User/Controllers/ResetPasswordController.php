<?php

namespace App\User\Controllers;

use App\User\UserService;
use App\Shared\JsonResponse;
use App\User\Exceptions\InvalidUserDataException;
use Psr\Http\Message\ServerRequestInterface;
use App\Shared\Exceptions\BadRequestException;

final class ResetPasswordController
{

    public function __construct(private UserService $userService) {}

    public function __invoke(ServerRequestInterface $request)
    {

        $userId = $request->pathParams["id"];
        $data = $request->getParsedBody();

        try {

            $this->userService->updatePassword($userId, $data);

            return JsonResponse::noContent();
        } catch (BadRequestException $e) {
            if ($e instanceof InvalidUserDataException) {
                return JsonResponse::badRequest(explode("#", $e->getMessage()));
            }
            
            return JsonResponse::badRequest([$e->getMessage()]);
        } catch (\Throwable $e) {
            echo "Error: " . $e->getTraceAsString() . PHP_EOL;

            return  JsonResponse::internalServerError($e->getMessage());
        }
    }
}
