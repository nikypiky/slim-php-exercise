<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

// Create Twig
$twig = Twig::create(__DIR__ . '/../templates', ['cache' => false]);

// Add Twig-View Middleware
$app->add(TwigMiddleware::create($app, $twig));

$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$app->get('/', function ($request, $response) {
    $view = Twig::fromRequest($request);

    return $view->render($response, 'login.html.twig');
});

// $app->post('/login', function ($request, $response) {
// 	include('../src/db.php');
// 	$data = $request->getParsedBody();

// 	$sql = "INSET INTO users (username, email, password) VALUES (?, ?, ?)"


//     $response->getBody()->write("POST successfull " . $data['username']);
// 	return $response;
// });


$app->get('/hello', function (Request $request, Response $response, $args) {
    include("../src/db.php");
    $sql = 'SELECT * FROM users';
    $result = $mysql->query($sql);
    $row = mysqli_fetch_assoc($result);
	if ($row) {
		$response->getBody()->write("success");
	}
    mysqli_close($mysql);
    // echo $row["id"] . "<br>";
    $response->getBody()->write("Customer I: " . $row["username"]);
    return $response;
});

$app->run();
