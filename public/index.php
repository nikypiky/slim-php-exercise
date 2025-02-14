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

include("../src/utils.php");

$app->get('/', function ($request, $response) {
	$view = Twig::fromRequest($request);
	if(isset($_SESSION["login_status"]) && $_SESSION["login_status"] == true) {
		return $response->withheader('location', '/user-table')->withstatus(302);
	} else {
		return $view->render($response, 'login-page.html.twig');
	}
});

$app->get('/user-table', function ($request, $response) {

	//reroute user if not logged in
	include('../src/db.php');
	$view = Twig::fromRequest($request);

	// get user data from database
	$result = $mysqli->query("SELECT id, username, email FROM users");
	$users = $result->fetch_all(MYSQLI_ASSOC);

	return $view->render($response, 'user-table.html.twig', [
		'users' => $users,
	]);
})->add(new CheckLoginMiddleware());

$app->get('/register-page', function ($request, $response) {
	$view = Twig::fromRequest($request);
	return $view->render($response, 'register-page.html.twig');
});

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
})->add(new CheckLoginMiddleware());

$app->get('/edit-user-page/{id}', function ($request, $response, array $args){
	$view = Twig::fromRequest($request);
	$id = $args['id'];

	return $view->render($response, 'edit-user-page.html.twig', [
		'id' => $id,
	]);
})->add(new CheckLoginMiddleware());

$app->post('/edit-user/{id}', function ($request, $response, array $args) {
	include('../src/db.php');
	$data = $request->getParsedBody();
	$id = $args['id'];

	$allowed_options = ['username', 'email', 'password'];
	if (!in_array($data["field"], $allowed_options)){
		die ("Please choose a option.");
	}
	$chosen_field = $data['field'];
	$new_data = $data['new_data'];
	if ($chosen_field === 'username') $error_message = checkUsername($new_data, $mysqli);
	if ($chosen_field === 'email') $error_message = checkEmail($new_data, $mysqli);
	if ($chosen_field === 'password') $error_message = checkPassword($new_data, $new_data, $mysqli);

	if ($error_message){
		$response->getBody()->write($error_message);
		return $response;
	}

	if ($chosen_field === 'password') {
		$new_data = password_hash($new_data, PASSWORD_DEFAULT);
	}

	try {
		$stmt = $mysqli->prepare("UPDATE users SET `$chosen_field` = ? WHERE id = ?");
		$stmt->bind_param("si", $new_data, $id);
		$stmt->execute();
	} catch (\Throwable $th) {
		$error_message = "Internal error, please try again later.";
		$response->getBody()->write($error_message);
		return $response;
	}
	return $response->withHeader('Location', '/')->withStatus(302);
});


$app->run();
