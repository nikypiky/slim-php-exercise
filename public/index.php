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

function checkRegistrationData($data, $mysqli)
{
	//check if username uses correct characters
	$username = $data["username"];
	$email = $data["email"];
	$password = $data["password"];
	if (!preg_match("/^[a-zA-Z-' ]*$/", $username)) {
		$error_message = "Only letters and white space allowed.";
	}
	$email = $data["email"];
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$error_message = "Invalid email format.";
	}
	$password = $data["password"];
	if ($password != $data["password_confirmation"]) {
		$error_message = "Passwords do not match.";
	}
	$stmt = $mysqli->prepare("SELECT username FROM users WHERE username = ?");
	$stmt->bind_param("s", $username);
	$stmt->execute();
	$result = $stmt->get_result();
	$user = $result->fetch_assoc();
	if ($user["username"]) {
		$error_message = "Username allready taken.";
	}
	$stmt = $mysqli->prepare("SELECT email FROM users WHERE email = ?");
	$stmt->bind_param("s", $email);
	$stmt->execute();
	$result = $stmt->get_result();
	$user = $result->fetch_assoc();
	if ($user["email"]) {
		$error_message = "E-mail allready taken.";
	}
	return $error_message;
}

function render_error($status_code, $destination, $error_message, $request, $response) {
	$view = Twig::fromRequest($request);
	$response = $response->withStatus($status_code);
	return $view->render($response, $destination, [
		'error_message' => $error_message,
	]);
}

$app->post('/register', function (Request $request, Response $response) {
	include('../src/db.php');
	$data = $request->getParsedBody();
	$error_message = checkRegistrationData($data, $mysqli);
	if ($error_message) {
		return render_error(406, 'register-page.html.twig', $error_message, $request, $response);
	}

	try {
		$stmt = $mysqli->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
		$stmt->bind_param("sss", $data["username"], $data["email"], $data["password"]);
		$stmt->execute();
	} catch (\Throwable $th) {
		$error_message = "Internal error, please try again later.";
		return render_error(500, 'register-page.html.twig', $error_message, $request, $response);
	}

	$response->withStatus(200);
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
