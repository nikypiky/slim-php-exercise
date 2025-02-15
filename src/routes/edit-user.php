<?php

$app->post('/edit-user/{id}', function ($request, $response, array $args) {
	include __DIR__ .'/../functions/db.php';
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
	if ($chosen_field === 'password') $error_message = checkPassword($new_data, $new_data);

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
