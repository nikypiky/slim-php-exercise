<?php
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\Middleware\CheckLoginMiddleware;

$app->get('/user-table', function ($request, $response) {



	//reroute user if not logged in
	$view = Twig::fromRequest($request);

	// get user data from database
	try {
		include __DIR__ . '/../functions/db.php';
		$result = $mysqli->query("SELECT id, username, email FROM users");
		$users = $result->fetch_all(MYSQLI_ASSOC);
	} catch (\Throwable $th) {
		$error_message = "Internal error, please try again later.";
		return render_error(500, 'user-table.html.twig', $th, $request, $response);
	}

	return $view->render($response, 'user-table.html.twig', [
		'users' => $users,
	]);
})->add(new CheckLoginMiddleware());
