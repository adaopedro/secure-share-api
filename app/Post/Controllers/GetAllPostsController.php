<?php

namespace App\Post\Controllers;

use App\Post\PostService;
use App\Shared\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

final class GetAllPostsController
{

    public function __construct( private PostService $postService ) {}

    public function __invoke(ServerRequestInterface $request)
    {

        $userId = $request->getQueryParams()["user_id"] ?? null;
        $postType = $request->getQueryParams()["post_type"] ?? null;

        try {
            $posts = $this->postService->getAll($userId, $postType);

            return JsonResponse::ok($posts);
        } catch (\Throwable $e) {
            echo "Error: " . $e->getTraceAsString() . PHP_EOL;

            return JsonResponse::internalServerError($e->getMessage());
        }
    }
}
