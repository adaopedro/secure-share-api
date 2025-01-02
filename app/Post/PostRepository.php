<?php

namespace App\Post;

use React\MySQL\ConnectionInterface;
use React\MySQL\QueryResult;
use React\Promise\Deferred;
use React\Promise\Promise;
use React\Promise\PromiseInterface;

final class PostRepository
{

    public function __construct(private ConnectionInterface $dbConnection) {}

    /** @return PromiseInterface<int> */
    public function addAsync(string $title, string $description, string $postType, string $userId,): PromiseInterface
    {

        $deferred = new Deferred();

        $this->dbConnection->query(
            "INSERT INTO posts (title, description, post_type, user_id) VALUES (?, ?, ?, ?)",
            [$title, $description, $postType, $userId]
        )->then(
            fn(QueryResult $result) => $deferred->resolve($result->insertId),
            fn(\Throwable $e) => $deferred->reject($e)
        );

        return $deferred->promise();
    }

    /** @return PromiseInterface<void> */
    public function updateCoverPhotoAsync(int $postId, string $filename): PromiseInterface
    {
        return $this->dbConnection->query(
            "UPDATE posts SET cover_photo = ? WHERE id = ?", [$filename, $postId]
        );
    }

    /** @return PromiseInterface<array> */
    public function getAllAsync(): PromiseInterface
    {
        $deferred = new Deferred();

        $this->dbConnection->query("SELECT * FROM posts")
            ->then(
                fn(QueryResult $result) => $deferred->resolve($result->resultRows),
                fn(\Throwable $e) => $deferred->reject($e)
            );

        return $deferred->promise();
    }

    /** @return PromiseInterface<array> */
    public function getAllByUserIdAndPostTypeAsync(int $userId, string $postType): PromiseInterface
    {
        $deferred = new Deferred();

        $this->dbConnection->query(
            "SELECT * FROM posts WHERE user_id = ? AND post_type = ?", [$userId, $postType]
        )->then(
            fn(QueryResult $result) => $deferred->resolve($result->resultRows),
            fn(\Throwable $e) => $deferred->reject($e)
        );

        return $deferred->promise();
    }

    /** @return PromiseInterface<array> */
    public function getAllByPostTypeAsync(string $postType): PromiseInterface
    {
        $deferred = new Deferred();

        $this->dbConnection->query(
            "SELECT * FROM posts WHERE post_type = ?", [$postType]
        )->then(
            fn(QueryResult $result) => $deferred->resolve($result->resultRows),
            fn(\Throwable $e) => $deferred->reject($e)
        );

        return $deferred->promise();
    }

    /** @return PromiseInterface<array> */
    public function getAllByUserIdAsync(int $userId): PromiseInterface
    {
        $deferred = new Deferred();

        $this->dbConnection->query(
            "SELECT * FROM posts WHERE user_id = ?", [$userId]
        )->then(
            fn(QueryResult $result) => $deferred->resolve($result->resultRows),
            fn(\Throwable $e) => $deferred->reject($e)
        );

        return $deferred->promise();
    }

    /** @return PromiseInterface<array> */
    public function getByIdAsync(int $postId): PromiseInterface
    {
        $deferred = new Deferred();

        $this->dbConnection->query(
            "SELECT * FROM posts WHERE id = ?", [$postId]
        )->then(
            fn(QueryResult $result) => $deferred->resolve(count($result->resultRows) > 0 ? $result->resultRows[0] : []),
            fn(\Throwable $e) => $deferred->reject($e)
        );

        return $deferred->promise();
    }

    /** @return PromiseInterface<void> */
    public function addViewToPostAsync(int $postId): PromiseInterface
    {
        return $this->dbConnection->query(
            "UPDATE posts SET views = views + 1 WHERE id = ?", [$postId]
        );
    }

    /** @return PromiseInterface<int> */
    public function getTotalOfPosts(): PromiseInterface
    {
        $deferred = new Deferred();

        $this->dbConnection->query(
            "SELECT COUNT(id) AS total_of_posts FROM posts",
        )->then(
            fn(QueryResult $result) => $deferred->resolve($result->resultRows[0]["total_of_posts"]),
            fn(\Throwable $e) => $deferred->reject($e)
        );

        return $deferred->promise();
    }

    /** @return PromiseInterface<array> */
    public function getTotalOfPostsByType(): PromiseInterface
    {
        $deferred = new Deferred();

        $this->dbConnection->query(
            "SELECT post_type, COUNT(id) AS total FROM posts GROUP BY post_type",
        )->then(
            fn(QueryResult $result) => $deferred->resolve($result->resultRows),
            fn(\Throwable $e) => $deferred->reject($e)
        );

        return $deferred->promise();
    }

    /** @return PromiseInterface<array> */
    public function getRecentPosts(int $max): PromiseInterface
    {
        $deferred = new Deferred();

        $this->dbConnection->query(
            "SELECT * FROM posts ORDER BY id DESC LIMIT $max",
        )->then(
            fn(QueryResult $result) => $deferred->resolve($result->resultRows),
            fn(\Throwable $e) => $deferred->reject($e)
        );

        return $deferred->promise();
    }
}
