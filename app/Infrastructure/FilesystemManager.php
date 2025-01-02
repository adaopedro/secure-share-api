<?php

namespace App\Infrastructure;

use React\Filesystem\FilesystemInterface;
use App\Shared\Contracts\FileManagerInterface;
use React\Promise\Promise;
use Clue\React\Redis\Client as RedisClient;
use function React\Async\await;

final class FilesystemManager implements FileManagerInterface
{

    public function __construct(private FilesystemInterface $filesystem, private RedisClient $redisClient ) {}

    public function upload(string $filename, mixed $data): string
    {
        
        return await(
            new Promise(function(callable $resolve, callable $reject) use ($filename, $data) {
                $this->filesystem
                    ->file($filename)
                    ->putContents( await($this->redisClient->get($data)) )
                    ->then(
                        function () use ($resolve, $filename, $data){
                            $resolve($filename);
                        },
                        fn (\Throwable $e) => $reject($e) 
                    );
            })
        );

    }

    public function delete(string $filename): bool
    {
        return true;
    }

    public function createDirectory(string $path): bool
    {
        return true;
    }

    public function deleteDirectory(string $path): bool
    {
        return true;
    }


}
