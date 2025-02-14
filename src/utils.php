<?php

function checkRegistrationData($data, $mysqli)
{
	include('db.php');

	//check if username uses correct characters
	$username = $data["username"];
	$email = $data["email"];
	$password = $data["password"];
	if (strlen($username < 6) || strlen($email) < 6 || strlen($password) < 6) {
		return "Credentials have to be at lease 6 characters long";
	}
	if (!preg_match("/^[a-zA-Z-' 0-9]*$/", $username)) {
		return "Only letters and white space allowed.";
	}
	$email = $data["email"];
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return "Invalid email format.";
	}
	$password = $data["password"];
	if ($password != $data["password_confirmation"]) {
		return "Passwords do not match.";
	}
	$stmt = $mysqli->prepare("SELECT username FROM users WHERE username = ?");
	$stmt->bind_param("s", $username);
	$stmt->execute();
	$result = $stmt->get_result();
	$user = $result->fetch_assoc();
	if ($user["username"]) {
		return "Username allready taken.";
	}
	$stmt = $mysqli->prepare("SELECT email FROM users WHERE email = ?");
	$stmt->bind_param("s", $email);
	$stmt->execute();
	$result = $stmt->get_result();
	$user = $result->fetch_assoc();
	if ($user["email"]) {
		return "E-mail allready taken.";
	}
}

function render_error($status_code, $template, $error_message, $request, $response)
{
	$view = Twig::fromRequest($request);
	$response = $response->withStatus($status_code);
	return $view->render($response, $template, [
		'error_message' => $error_message,
	]);
}
