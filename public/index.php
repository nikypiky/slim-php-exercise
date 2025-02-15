<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Slim\Middleware\MethodOverrideMiddleware;
use App\Middleware\CheckLoginMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

// Create Twig
$twig = Twig::create(__DIR__ . '/../templates', ['cache' => false]);

// Add Twig-View Middleware
$app->add(TwigMiddleware::create($app, $twig));

$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// start session to track user login
session_start();

// Add MethodOverride middleware
$methodOverrideMiddleware = new MethodOverrideMiddleware();
$app->add($methodOverrideMiddleware);

$mysqli = include __DIR__ . '/../src/functions/db.php';

include("../src/functions/utils.php");

include("../src/routes/root.php");

include("../src/routes/login.php");

include("../src/routes/register-page.php");

include("../src/routes/register.php");

include("../src/routes/user-table.php");

include("../src/routes/del-user.php");

include("../src/routes/edit-user-page.php");

include("../src/routes/edit-user.php");

$app->run();
