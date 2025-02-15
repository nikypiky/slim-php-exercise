<?php
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\Middleware\CheckLoginMiddleware;

$app->get('/user-table', function ($request, $response) {

	include __DIR__ . '/../functions/db.php';
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
	//use is no a admin
	if (!$is_admin['admin_id']) {
		try {
			$result = $mysqli->query("SELECT id, username, email FROM users WHERE id = $user_id");
			$users = $result->fetch_all(MYSQLI_ASSOC);
		} catch (\Throwable $th) {
			$error_message = "Internal error, please try again later.";
			return render_error(500, 'user-table.html.twig', $error_message, $request, $response);
	}}
	//user is a admin
	else {
		try {
			$result = $mysqli->query("SELECT id, username, email FROM users");
			$users = $result->fetch_all(MYSQLI_ASSOC);
		} catch (\Throwable $th) {
			$error_message = "Internal error, please try again later.";
			return render_error(500, 'user-table.html.twig', $error_message, $request, $response);
		}
	}

	return $view->render($response, 'user-table.html.twig', [
		'users' => $users,
	]);
})->add(new CheckLoginMiddleware());
