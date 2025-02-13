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

$app->get('/register-page', function ($request, $response) {
	$view = Twig::fromRequest($request);

	return $view->render($response, 'register-page.html.twig');
});

$app->post('/register', function (Request $request, Response $response) {
	include('../src/db.php');
	$data = $request->getParsedBody();

	//check if username uses correct characters
	$username = $data["username"];
	if (!preg_match("/^[a-zA-Z-' ]*$/", $name)) {
		$error_message = "Only letters and white space allowed";
	}
	$email = $data["email"];
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$error_message = "Invalid email format";
	}
	if ($error_message) {
		$view = Twig::fromRequest($request);

		return $view->render($response, 'register-page.html.twig', [
			'error_message' => $error_message,
		]);
	}


	$stmt = $mysqli->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
	$stmt->bind_param("sss", $data["username"], $data["email"], $data["password"]);
	$stmt->execute();


	$response->getBody()->write("POST successfull " . $data["username"] . $data["email"] . $data["password"]);
	return $response;
});


$app->get('/hello', function (Request $request, Response $response, $args) {
	include("../src/db.php");
	$sql = 'SELECT * FROM users';
	$result = $mysqli->query($sql);
	$row = mysqli_fetch_assoc($result);
	if ($row) {
		$response->getBody()->write("success");
	}
	mysqli_close($mysqli);
	// echo $row["id"] . "<br>";
	$response->getBody()->write("Customer I: " . $row["username"]);
	return $response;
});

$app->run();
