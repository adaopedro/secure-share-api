<?php

namespace App\User\Controllers;

use App\User\UserService;
use App\User\UserValidator;
use App\Shared\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use App\Shared\Exceptions\BadRequestException;
use App\User\Exceptions\InvalidUserDataException;

final class CreateUserController
{

    public function __construct(private UserService $userService) {}

    public function __invoke(ServerRequestInterface $request)
    {
        
        $data = $request->getParsedBody();

        try {
            $userId = $this->userService->create($data);
            
            return JsonResponse::created([
                "message" => "created",
                "data" => ["user_id" => $userId]
            ]);
        } catch (BadRequestException $e) {
            if ($e instanceof InvalidUserDataException) {
                return JsonResponse::badRequest(explode("#", $e->getMessage()));
            }
            
            return JsonResponse::badRequest([$e->getMessage()]);
        } catch (\Throwable $e) {
            echo "Error: " . $e->getTraceAsString() . PHP_EOL;
            
            return JsonResponse::internalServerError($e->getMessage());
        }
    }
}
