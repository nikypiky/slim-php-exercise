<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Slim\Middleware\MethodOverrideMiddleware;

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

$app->get('/', function ($request, $response) {
	$view = Twig::fromRequest($request);
	if(isset($_SESSION["login_status"]) && $_SESSION["login_status"] == true) {
		return $response->withheader('location', '/user-table')->withstatus(302);
	} else {
		return $view->render($response, 'login-page.html.twig');
	}
});

$app->get('/user-table', function ($request, $response) {
	include('../src/db.php');
	$view = Twig::fromRequest($request);

	// get user data from database
	$result = $mysqli->query("SELECT id, username, email FROM users");
	$users = $result->fetch_all(MYSQLI_ASSOC);

	return $view->render($response, 'user-table.html.twig', [
		'users' => $users,
	]);
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
	if (strlen($username < 6) || strlen($email) < 6 || strlen($password) < 6) {
		return "Credentials have to be at lease 6 characters long";
	}
	if (!preg_match("/^[a-zA-Z-' 0-9]*$/", $username)) {
		return "Only letters and white space allowed.";
	}
	$email = $data["email"];
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return "Invalid email format.";
	}
	$password = $data["password"];
	if ($password != $data["password_confirmation"]) {
		return "Passwords do not match.";
	}
	$stmt = $mysqli->prepare("SELECT username FROM users WHERE username = ?");
	$stmt->bind_param("s", $username);
	$stmt->execute();
	$result = $stmt->get_result();
	$user = $result->fetch_assoc();
	if ($user["username"]) {
		return "Username allready taken.";
	}
	$stmt = $mysqli->prepare("SELECT email FROM users WHERE email = ?");
	$stmt->bind_param("s", $email);
	$stmt->execute();
	$result = $stmt->get_result();
	$user = $result->fetch_assoc();
	if ($user["email"]) {
		return "E-mail allready taken.";
	}
}

function render_error($status_code, $template, $error_message, $request, $response)
{
	$view = Twig::fromRequest($request);
	$response = $response->withStatus($status_code);
	return $view->render($response, $template, [
		'error_message' => $error_message,
	]);
}

$app->post('/login', function (Request $request, Response $response) {
	include('../src/db.php');
	$data = $request->getParsedBody();

	// querry database
	try {
		$stmt = $mysqli->prepare("SELECT * FROM users WHERE username = (?);");
		$stmt->bind_param("s", $data["username"]);
		$stmt->execute();
		$result = $stmt->get_result();
		$row = mysqli_fetch_assoc($result);
	} catch (\Throwable $th) {
		$error_message = "Internal error, please try again later.";
		return render_error(500, 'login-page.html.twig', $error_message, $request, $response);
	}

	// check login information
	if (!$row["username"]) {
		return render_error(401, 'login-page.html.twig', 'Incorrect username.', $request, $response);
	}
	if (!password_verify($data["password"], $row["password"])){
		return render_error(401, 'login-page.html.twig', 'Incorrect password.', $request, $response);
	}

	// set session variable
	$_SESSION["username"] = $row["username"];
	$_SESSION["login_status"] = true;

	// $app->redirect('/register_page', '/', 200);
	// $response->getBody()->write("Logged in successfully");
	return $response->withheader('location', '/')->withstatus(302);
});

$app->post('/register', function (Request $request, Response $response) {
	$data = $request->getParsedBody();

	// check correctness of user input
	$error_message = checkRegistrationData($data, $mysqli);
	if ($error_message) {
		return render_error(406, 'register-page.html.twig', $error_message, $request, $response);
	}

	// query database
	try {
		include('../src/db.php');
		$hash = password_hash($data['password'], PASSWORD_DEFAULT);
		$stmt = $mysqli->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
		$stmt->bind_param("sss", $data["username"], $data["email"], $hash);
		$stmt->execute();
	} catch (\Throwable $th) {
		$error_message = "Internal error, please try again later.";
		return render_error(500, 'register-page.html.twig', $error_message, $request, $response);
	}

	// set session variable
	$_SESSION["username"] = $row["username"];
	$_SESSION["login_status"] = true;

	return $response->withHeader('Location', '/')->withStatus(302);
});

$app->delete('/del-user/{id}', function ($request, $response, array $args) {
	$id = $args['id'];
	$id_int =  intval($id);

	try {
		include('../src/db.php');
		$stmt = $mysqli->prepare("DELETE FROM users WHERE id = ?");
		$stmt->bind_param("i", $id_int);
		$stmt->execute();
	} catch (\Throwable $th) {
		$error_message = "Internal error, please try again later.";
		return render_error(500, 'user-table.html.twig', $th, $request, $response);
	}
		return $response->withHeader('Location', '/user-table')->withStatus(302);
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
