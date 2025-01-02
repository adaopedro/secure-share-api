<?php
    namespace App\Auth;

    use App\Shared\JsonResponse;
    use Psr\Http\Message\ServerRequestInterface;
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
    use Firebase\JWT\ExpiredException;
    use UnexpectedValueException;

    final class ProtectedRoute {

        public function __construct(private $middleware) {}

        public function __invoke(ServerRequestInterface $request) {

            try {
                
                if ( ! key_exists("Authorization", $request->getHeaders()) ) {
                    return JsonResponse::badRequest(["Token nao encontrado"]);
                }

                [, $token] = explode(
                    " ",
                    $request->getHeaderLine("Authorization")
                );
                
                $this->authenticate($token);

                return call_user_func($this->middleware, $request);

            } catch (ExpiredException  $e) {
                return JsonResponse::unauthorized();
            } catch (UnexpectedValueException $e) {
                return JsonResponse::badRequest(["Token invalido"]);
            }
            catch (\Throwable $e) {
                echo "Error: " . $e->getTraceAsString() . PHP_EOL;

                return JsonResponse::internalServerError ($e->getMessage());
            }

        }

        private function authenticate (string $jwt): true {
            JWT::$leeway = 60;
            $key = "secureshare";

            JWT::decode($jwt, new Key($key, "HS256"));

            return true;
        }



    }