<?php
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\Middleware\CheckLoginMiddleware;

$app->get('/user-table', function ($request, $response) {

	include __DIR__ . '/../functions/db.php';

	//reroute user if not logged in
	include('../functions/db.php');
	$view = Twig::fromRequest($request);

	// get user data from database
	$result = $mysqli->query("SELECT id, username, email FROM users");
	$users = $result->fetch_all(MYSQLI_ASSOC);

	return $view->render($response, 'user-table.html.twig', [
		'users' => $users,
	]);
})->add(new CheckLoginMiddleware());
