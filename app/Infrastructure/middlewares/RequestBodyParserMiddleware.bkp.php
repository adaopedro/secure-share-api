<?php

namespace App\Infrastructure\middlewares;

use React\Promise\Deferred;
use React\Http\Message\Response;
use Psr\Http\Message\ServerRequestInterface;

final class RequestBodyParserMiddleware
{

    public function __invoke(ServerRequestInterface $request, callable $next)
    {
        $deferred = new Deferred();

        /** @var ReadableStreamInterface $body */
        $body = $request->getBody();

        $buffer = "";

        $body->on("data", function ($chunk) use (&$buffer) {
            $buffer .= $chunk;
        });

        $body->on("close", function () use ($deferred, &$buffer, $request, $next) {

            $data = [];

            if ($buffer !== "") {
                $data = (array) json_decode($buffer);
            }

            if (json_last_error() !== JSON_ERROR_NONE) {
                $deferred->resolve(
                    new Response(
                        Response::STATUS_BAD_REQUEST,
                        ["Content-Type" => "application/json"],
                        json_encode(["Request body error" => json_last_error_msg()])
                    )
                );
            }

            $request = $request->withParsedBody($data);


            $deferred->resolve( $next($request) );
        });

   

        return $deferred->promise();
    }
}
