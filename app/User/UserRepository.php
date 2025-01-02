<?php

namespace App\User;

use Exception;
use React\MySQL\ConnectionInterface;
use React\MySQL\QueryResult;
use React\Promise\Deferred;
use React\Promise\Promise;
use React\Promise\PromiseInterface;

final class UserRepository
{

    public function __construct(private ConnectionInterface $dbConnection) {}

    /** @return PromiseInterface<int> */
    public function addAsync(
        string $firstName,
        string $lastName,
        string $nipId,
        string $contact,
        string $email,
        string $password,
        bool $isAdmin
    ): PromiseInterface {

        $deferred = new Deferred();

        $this->dbConnection->query(
            "INSERT INTO users (first_name, last_name, nip_id, contact, email, password, is_admin) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$firstName, $lastName, $nipId,  $contact, $email, $password, $isAdmin]
        )->then(
            fn(QueryResult $result) => $deferred->resolve($result->insertId),
            fn(\Throwable $e) => $deferred->reject($e)
        );

        return $deferred->promise();
    }

    /** @return PromiseInterface<void> */
    public function updatePasswordAsync(int $userId, string $password): PromiseInterface
    {

        $deferred = new Deferred();

        $this->dbConnection->query("UPDATE users SET password = ? WHERE id = ?", [$password, $userId])
            ->then(
                fn(QueryResult $result) => $deferred->resolve(),
                fn(\Throwable $e) => $deferred->reject($e)
            );

        return $deferred->promise();
    }

    /** @return PromiseInterface<void> */
    public function updateProfilePictureAsync(int $userId, string $filename): PromiseInterface
    {

        $deferred = new Deferred();

        $this->dbConnection->query("UPDATE users SET profile_picture = ? WHERE id = ?", [$filename, $userId])
            ->then(
                fn(QueryResult $result) => $deferred->resolve(),
                fn(\Throwable $e) => $deferred->reject($e)
            );

        return $deferred->promise();
    }

    /** @return PromiseInterface<array> */
    public function getAllAsync(): PromiseInterface
    {
        $deferred = new Deferred();

        $this->dbConnection->query("SELECT * FROM users")
            ->then(
                fn(QueryResult $result) => $deferred->resolve($result->resultRows),
                fn(\Throwable $e) => $deferred->reject($e)
            );

        return $deferred->promise();
    }

    /** @return PromiseInterface<array> */
    public function getByIdAsync(int $userId): PromiseInterface
    {
        $deferred = new Deferred();

        $this->dbConnection->query("SELECT * FROM users WHERE id = ?", [$userId])
            ->then(
                fn(QueryResult $result) => $deferred->resolve(count($result->resultRows) > 0 ? $result->resultRows[0] : []),
                fn(\Throwable $e) =>  $deferred->reject($e)
            );

        return $deferred->promise();
    }

    /** @return PromiseInterface<array> */
    public function getByNipIdAsync(string $nipId): PromiseInterface
    {
        $deferred = new Deferred();

        $this->dbConnection->query("SELECT * FROM users WHERE nip_id = ?", [$nipId])
            ->then(
                fn(QueryResult $result) => $deferred->resolve(count($result->resultRows) > 0 ? $result->resultRows[0] : []),
                fn(\Throwable $e) =>  $deferred->reject($e)
            );

        return $deferred->promise();
    }

    /** @return PromiseInterface<array> */
    public function getByEmailAsync(string $email): PromiseInterface
    {
        $deferred = new Deferred();

        $this->dbConnection->query("SELECT * FROM users WHERE email = ?", [$email])
            ->then(
                fn(QueryResult $result) => $deferred->resolve(count($result->resultRows) > 0 ? $result->resultRows[0] : []),
                fn(\Throwable $e) => $deferred->reject($e)
            );

        return $deferred->promise();
    }

    /** @return PromiseInterface<int> */
    public function getTotalOfUsers(): PromiseInterface
    {
        $deferred = new Deferred();

        $this->dbConnection->query("SELECT COUNT(id) AS total_of_users FROM users",)
            ->then(
                fn(QueryResult $result) => $deferred->resolve($result->resultRows[0]["total_of_users"]),
                fn(\Throwable $e) => $deferred->reject($e)
            );

        return $deferred->promise();
    }
}
