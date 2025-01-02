<?php

namespace App\Post\Controllers;

use App\Shared\JsonResponse;
use App\Shared\Exceptions\BadRequestException;
use App\Post\PostService;
use Psr\Http\Message\ServerRequestInterface;

final class AddViewToPostByIdController
{

    public function __construct( private PostService $postService ) {}

    public function __invoke(ServerRequestInterface $request)
    {

        $postId = $request->pathParams["id"] ?? "";

        try {
            $this->postService->addViewToPost($postId);

            return JsonResponse::noContent();

        } catch (BadRequestException $e) {
            return JsonResponse::badRequest([$e->getMessage()]);
        } catch (\Throwable $e) {
            echo "Error: " . $e->getTraceAsString() . PHP_EOL;

            return JsonResponse::internalServerError($e->getMessage());
        }
    }
}
