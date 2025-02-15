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
		//create user
		$hash = password_hash($data['password'], PASSWORD_DEFAULT);
		$stmt = $mysqli->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
		$stmt->bind_param("sss", $data["username"], $data["email"], $hash);
		$stmt->execute();
		//git user id
		$stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
		$stmt->bind_param('s', $data["username"]);
		$stmt->execute();
		$result = $stmt->get_result();
		$row = mysqli_fetch_assoc($result);
	} catch (\Throwable $th) {
		$error_message = "Internal error, please try again later.";
		return render_error(500, 'register-page.html.twig', $th, $request, $response);
	}

	// set session variable
	$_SESSION["id"] = $row["id"];
	$_SESSION["login_status"] = true;

	return $response->withHeader('Location', '/')->withStatus(302);
});
