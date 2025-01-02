<?php

namespace App\PostAttachment;

use React\MySQL\ConnectionInterface;
use React\MySQL\QueryResult;
use React\Promise\Deferred;
use React\Promise\Promise;
use React\Promise\PromiseInterface;

final class PostAttachmentRepository
{

    public function __construct(private ConnectionInterface $dbConnection) {}

    /** @return PromiseInterface<int> */
    public function addAsync(string $name, string $postId): PromiseInterface
    {

        $deferred = new Deferred();

        $this->dbConnection->query(
            "INSERT INTO post_attachments (name, post_id) VALUES (?, ?)", [$name, $postId]
        )->then(
            fn(QueryResult $result) => $deferred->resolve($result->insertId),
            fn(\Throwable $e) => $deferred->reject($e)
        );

        return $deferred->promise();
    }

    /** @return PromiseInterface<void> */
    public function updateFilenameAsync(int $postAttachmentId, string $filename): PromiseInterface
    {
        return $this->dbConnection->query(
            "UPDATE post_attachments SET file = ? WHERE id = ?", [$filename, $postAttachmentId]
        );
    }

    /** @return PromiseInterface<array> */
    public function getAllByPostIdAsync(int $postId): PromiseInterface
    {
        $deferred = new Deferred();

        $this->dbConnection->query("SELECT * FROM post_attachments WHERE post_id = ?", [$postId])
            ->then(
                fn(QueryResult $result) => $deferred->resolve($result->resultRows),
                fn(\Throwable $e) => $deferred->reject($e)
            );

        return $deferred->promise();
    }
}
