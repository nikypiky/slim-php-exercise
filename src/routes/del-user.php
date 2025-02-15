<?php

use App\Middleware\CheckLoginMiddleware;

$app->delete('/del-user/{id}', function ($request, $response, array $args) {
	$id = $args['id'];
	$id_int =  intval($id);

	try {
		include __DIR__ .'/../functions/db.php';
		$stmt = $mysqli->prepare("DELETE FROM users WHERE id = ?");
		$stmt->bind_param("i", $id_int);
		$stmt->execute();
		$stmt = $mysqli->prepare("DELETE FROM admins WHERE admin_id = ?");
		$stmt->bind_param("i", $id_int);
		$stmt->execute();
	} catch (\Throwable $th) {
		$error_message = "Internal error, please try again later.";
		return render_error(500, 'user-table.html.twig', $th, $request, $response);
	}
	if ($id == $_SESSION['id']){
		session_destroy();
	}
		return $response->withHeader('Location', '/')->withStatus(302);
})->add(new CheckLoginMiddleware());
