<?php

namespace App\Dashboard;

use App\Post\PostService;
use App\User\UserService;

final class DashboardService
{

    public function __construct(private UserService $userService, private PostService $postService) {}

    public function getData(): array
    {
        return [
            "total_of_users" => $this->userService->getTotalOfUsers(),
            "total_of_posts" => $this->postService->getTotalOfPosts(),
            "total_posts_by_type" => $this->postService->getTotalOfPostsByType(),
            "recent_posts" => $this->postService->getRecentPosts(3)
        ];
    }
}
