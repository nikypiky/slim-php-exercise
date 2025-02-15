<?php

$app->post('/login', function ($request, $response) {
	include __DIR__ . '/../functions/db.php';
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
	$_SESSION["id"] = $row["id"];
	$_SESSION["login_status"] = true;
	return $response->withheader('location', '/')->withstatus(302);
});
