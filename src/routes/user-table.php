<?php
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\Middleware\CheckLoginMiddleware;

$app->get('/user-table', function ($request, $response) {

	include __DIR__ . '/../functions/db.php';
	//reroute user if not logged in
	$view = Twig::fromRequest($request);
	$user_id = $_SESSION['id'];

	//check if user is admin
	try {
		$result = $mysqli->query("SELECT admin_id FROM admins WHERE admin_id = $user_id");
		$is_admin = $result->fetch_assoc();
	} catch (\Throwable $th) {
		$error_message = "Internal error, please try again later.";
		return render_error(500, 'user-table.html.twig', $th, $request, $response);
	}

	//execute query according to users rights
	if (!$is_admin['admin_id']) {
		try {
			$result = $mysqli->query("SELECT id, username, email FROM users WHERE id = $user_id");
			$users = $result->fetch_all(MYSQLI_ASSOC);
		} catch (\Throwable $th) {
			$error_message = "Internal error, please try again later.";
			return render_error(500, 'user-table.html.twig', $error_message, $request, $response);
	}} else {
		try {
			$result = $mysqli->query("SELECT id, username, email FROM users");
			$users = $result->fetch_all(MYSQLI_ASSOC);
		} catch (\Throwable $th) {
			$error_message = "Internal error, please try again later.";
			return render_error(500, 'user-table.html.twig', $error_message, $request, $response);
		}
	}


	// get user data from database
	return $view->render($response, 'user-table.html.twig', [
		'users' => $users,
	]);
})->add(new CheckLoginMiddleware());
