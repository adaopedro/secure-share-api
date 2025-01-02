<?php

namespace App\Post\Controllers;

use App\Post\PostService;
use App\Shared\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use App\Shared\Exceptions\BadRequestException;

final class GetPostByIdController
{

    public function __construct( private PostService $postService ) {}

    public function __invoke(ServerRequestInterface $request)
    {

        $postId = $request->pathParams["id"];

        try {

            $post = $this->postService->getById($postId);

            return JsonResponse::ok($post);

        } catch (BadRequestException $e) {
            return JsonResponse::badRequest([$e->getMessage()]);
        } catch (\Throwable $e) {
            echo "Error: " . $e->getTraceAsString() . PHP_EOL;

            return JsonResponse::internalServerError($e->getMessage());
        }
    }
}
