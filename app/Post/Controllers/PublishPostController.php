<?php

namespace App\Post\Controllers;

use App\Post\PostService;
use App\Shared\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use App\Shared\Exceptions\BadRequestException;
use App\Post\Exceptions\InvalidPostDataException;

final class PublishPostController
{

    public function __construct(private PostService $postService) {}

    public function __invoke(ServerRequestInterface $request)
    {
        $data = $request->getParsedBody();

        try {
            $postId = $this->postService->publish($data);

            return JsonResponse::created([
                "message" => "created",
                "data" => ["post_id" => $postId]
            ]);
        } catch (BadRequestException $e) {
            if ($e instanceof InvalidPostDataException) {
                return JsonResponse::badRequest(explode("#", $e->getMessage()));
            }

            return JsonResponse::badRequest([$e->getMessage()]);
        } catch (\Throwable $e) {
            echo "Error: " . $e->getTraceAsString() . PHP_EOL;

            return JsonResponse::internalServerError($e->getMessage());
        }
    }
}
