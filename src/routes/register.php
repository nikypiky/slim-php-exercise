<?php

$app->post('/register', function ($request, $response){

	$data = $request->getParsedBody();

	// check correctness of user input
	$error_message = checkRegistrationData($data, $mysqli);
	if ($error_message) {
		return render_error(406, 'register-page.html.twig', $error_message, $request, $response);
	}

	// query database
	try {
		include __DIR__ .'/../functions/db.php';
		$hash = password_hash($data['password'], PASSWORD_DEFAULT);
		$stmt = $mysqli->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
		$stmt->bind_param("sss", $data["username"], $data["email"], $hash);
		$stmt->execute();
	} catch (\Throwable $th) {
		$error_message = "Internal error, please try again later.";
		return render_error(500, 'register-page.html.twig', $th, $request, $response);
	}

	// set session variable
	$_SESSION["username"] = $row["username"];
	$_SESSION["login_status"] = true;

	return $response->withHeader('Location', '/')->withStatus(302);
});
