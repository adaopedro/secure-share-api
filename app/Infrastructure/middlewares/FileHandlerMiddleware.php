<?php

namespace App\Infrastructure\middlewares;

use Mimey\MimeTypes;
use React\Http\Message\Response;
use Psr\Http\Message\ServerRequestInterface;
use React\ChildProcess\Process;
use React\Filesystem\FilesystemInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use function React\Async\await;
use function React\Promise\resolve;

final class FileHandlerMiddleware
{

    public function __construct(private string $uploadsDirectory, private FilesystemInterface $filesystem) {}

    public function __invoke(ServerRequestInterface $request, callable $next)
    {

        $requestedPath = trim($request->getUri()->getPath(), " \n\r\t\v\0/");

        preg_match("/^assets\/.*\w+\.\w+$/", $requestedPath, $matches);

        if (!$matches) {
            return resolve($next($request))->then(fn($response) => $response);
        }

        $requestedPath = str_replace("assets/", "", $requestedPath);

        if ($this->checkIfFileExists($requestedPath) === false) {
            return new Response(
                Response::STATUS_BAD_REQUEST,
                ["Content-Type" => "application/json"],
                json_encode(["error" => "Ficheiro nao encontrado"])
            );
        }

        try {

            $promise =  $this->readFileThroughChildProcessAsync($requestedPath) ;

            // $promise = $this->readFileAsync($requestedPath);
            
            //Debug memory usage
            echo "Memory usage after response (in FileHandlerMiddleware.php): " . round ( (memory_get_peak_usage()/1024)/1024 ) . "MB \n";

            return resolve( $promise );

        } catch (\Throwable $e) {
            var_dump($e->getMessage());
        } 
    }

    private function checkIfFileExists(string $filename): bool
    {

        $deferred = new Deferred();

        $command = "test -f " . $filename . " && echo '1' || echo '0'";

        $process = new Process($command, $this->uploadsDirectory);
        $process->start();

        $process->stdout->on(
            "data",
            function (string $result) use ($deferred) {
                $deferred->resolve((bool) ((int) $result));
            }
        );

        return await($deferred->promise());
    }
   
    private function readFileAsync(string $filename): PromiseInterface
    {

        $filename = $this->uploadsDirectory . DIRECTORY_SEPARATOR . $filename;

        $deferred = new Deferred();

        $mimeType = (new MimeTypes)->getMimeType(pathinfo($filename, PATHINFO_EXTENSION)) ?? "application/octet-stream";

        $this->filesystem
            ->file($filename)
            ->getContents()
            ->then(
                function(string $contents) use ($deferred, $mimeType) { 
                    $deferred->resolve(
                        new Response(
                            Response::STATUS_OK,
                            ["Content-Type" => $mimeType],
                            $contents
                        )
                    );
                },
                function(\Throwable $e) use ($deferred) { 
                    $deferred->reject($e);
                },
            );

        return $deferred->promise();
    }

    /**
     * This approach is faster than using react/filesystem getContents() implemented on $this->readFileAsync() method
     */
    private function readFileThroughChildProcessAsync(string $filename): PromiseInterface
    {

        $deferred = new Deferred();

        $mimeType = (new MimeTypes)->getMimeType(pathinfo($filename, PATHINFO_EXTENSION)) ?? "application/octet-stream";

        $command = "cat " . $filename;
        $process = new Process($command, $this->uploadsDirectory);
        $process->start();

        $buffer = "";

        $process->stdout->on(
            "data",
            function ($chunks) use (&$buffer) {
                $buffer .= $chunks;
            }
        );

        $process->on('exit', function($exitCode, $termSignal) {
            echo 'Process exited with code ' . $exitCode . PHP_EOL;
        });

        $process->stdout->on(
            "close",
            function () use (&$buffer, $mimeType, $deferred) {
                $deferred->resolve(
                    new Response(
                        Response::STATUS_OK,
                        ["Content-Type" => $mimeType],
                        $buffer
                    )
                );
            }
        );

        return $deferred->promise();
    }
}
