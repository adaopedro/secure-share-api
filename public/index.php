<?php

require dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

use App\Auth\Guard;
use App\Auth\AuthService;
use App\Post\PostService;
use App\User\UserService;
use React\EventLoop\Loop;
use React\Http\HttpServer;
use App\User\UserValidator;
use App\Post\PostRepository;
use App\User\UserRepository;
use FastRoute\RouteCollector;
use App\Infrastructure\Router;
use FastRoute\RouteParser\Std;
use React\Socket\SocketServer;
use App\Shared\DocumentService;
use React\Filesystem\Filesystem;
use App\Dashboard\DashboardService;
use React\MySQL\Factory as MySQLFactory;
use App\Auth\Controllers\LoginController;
use App\Infrastructure\FilesystemManager;
use FastRoute\DataGenerator\GroupCountBased;
use App\Dashboard\GetDashboardDataController;
use App\PostAttachment\PostAttachmentService;
use App\User\Controllers\CreateUserController;
use App\Post\Controllers\GetAllPostsController;
use App\Post\Controllers\GetPostByIdController;
use App\Post\Controllers\PublishPostController;
use App\User\Controllers\GetAllUsersController;
use App\User\Controllers\GetUserByIdController;
use Sikei\React\Http\Middleware\CorsMiddleware;
use App\PostAttachment\PostAttachmentRepository;
use App\User\Controllers\ResetPasswordController;
use \Clue\React\Redis\Factory as RedisClientFactory;
use App\Post\Controllers\AddViewToPostByIdController;
use React\Http\Middleware\StreamingRequestMiddleware;
use App\Auth\Controllers\PasswordResetRequestController;
use App\Infrastructure\middlewares\FileHandlerMiddleware;
use App\Infrastructure\middlewares\RequestBodyParserMiddleware;
use App\Post\PostValidator;
use React\Http\Middleware\LimitConcurrentRequestsMiddleware;

$loop = Loop::get();

$ipAddress = "0.0.0.0";
$port = $_ENV['PORT'] ?? 8080;
$socket = new SocketServer("$ipAddress:$port",);

$filesystem = Filesystem::create($loop);

$mySqlConnectionUri = $_ENV['DB_USER']
    . ":"
    . $_ENV['DB_PASSWORD']
    . "@"
    . $_ENV['DB_HOST']
    . "/"
    . $_ENV['DB_NAME'];

$mySql = new MySQLFactory();
$mySqlConnection = $mySql->createLazyConnection($mySqlConnectionUri);

$redisClient =  new RedisClientFactory()->createLazyClient("cache:6379");

$userRepository = new UserRepository($mySqlConnection);
$postRepository = new PostRepository($mySqlConnection);
$postAttachmentRepository = new PostAttachmentRepository($mySqlConnection);

$uploadsDirectory = dirname(__DIR__) . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR . "Uploads";

$postAttachmentService = new PostAttachmentService($postAttachmentRepository);
$documentService = new DocumentService(new FilesystemManager($filesystem, $redisClient));
$userService = new UserService($userRepository, $documentService, $redisClient, new UserValidator, $uploadsDirectory);
$postService = new PostService($postRepository, $postAttachmentService, $documentService, $redisClient, $userService, new PostValidator, $uploadsDirectory);
$authService = new AuthService($userService);
$dashboardService = new DashboardService($userService, $postService);


$routes = new RouteCollector(new Std(), new GroupCountBased());

$routes->post(
    "/users",
    Guard::protect(
        new CreateUserController($userService)
    )
);
$routes->put(
    "/users/{id:\d+}/password",
    Guard::protect(new ResetPasswordController($userService))
);
$routes->get(
    "/users",
    Guard::protect(new GetAllUsersController($userService))
);
$routes->get(
    "/users/{id:\d+}",
    Guard::protect(new GetUserByIdController($userService))
);
$routes->post(
    "/posts",
    Guard::protect(
        new PublishPostController($postService)
    )
);
$routes->get(
    "/posts",
    Guard::protect(new GetAllPostsController($postService))
);
$routes->get(
    "/posts/{id:\d+}",
    Guard::protect(new GetPostByIdController($postService))
);
$routes->post(
    "/posts/{id:\d+}/views",
    Guard::protect(new AddViewToPostByIdController($postService))
);
$routes->post(
    "/auth/login",
     new LoginController($authService)
);
$routes->post(
    "/auth/password-reset-request", 
    new PasswordResetRequestController($authService)
);
$routes->get(
    "/dashboard/data",
    Guard::protect(new GetDashboardDataController($dashboardService))
);

$server = new HttpServer(
    new CorsMiddleware(require dirname(__DIR__) . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "cors-settings.php"),
    new LimitConcurrentRequestsMiddleware(1),
    new FileHandlerMiddleware($uploadsDirectory, $filesystem),
    new StreamingRequestMiddleware,
    new RequestBodyParserMiddleware,
    new Router($routes),
);

$server->on(
    "error",
    function (\Throwable $e) {
        echo "Error => " . $e->getMessage();
        echo "Previous error => " . $e?->getPrevious()->getMessage();
    }
);

$server->listen($socket);

echo "HTTP Server powered by ReactPHP. Running on "
    . str_replace("tcp", "http",  $socket->getAddress())
    . " address."
    . PHP_EOL;

$loop->run();