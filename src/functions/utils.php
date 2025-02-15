<?php
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

function checkUsername($username, $mysqli){
	if (strlen($username) === 0) {
		return "Please insert username.";
	}
	if (strlen($username) >= 255) {
		return "Username too long.";
	}
	if (!preg_match("/^[a-zA-Z-' 0-9]*$/", $username)) {
		return "Only letters and white space allowed.";
	}
	$stmt = $mysqli->prepare("SELECT username FROM users WHERE username = ?");
	$stmt->bind_param("s", $username);
	$stmt->execute();
	$result = $stmt->get_result();
	$user = $result->fetch_assoc();
	if ($user["username"]) {
		return "Username allready taken.";
	}
	return null;
}

function checkEmail($email, $mysqli) {
	if (strlen($email) === 0) {
		return "Please insert email.";
	}
	if (strlen($email) >= 255) {
		return "Email too long.";
	}
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return "Invalid email format.";
	}
	$stmt = $mysqli->prepare("SELECT email FROM users WHERE email = ?");
	$stmt->bind_param("s", $email);
	$stmt->execute();
	$result = $stmt->get_result();
	$user = $result->fetch_assoc();
	if ($user["email"]) {
		return "E-mail allready taken.";
	}
	return null;
}

function checkPassword($password, $confirm_password){
	if (strlen($password) === 0) {
		return "Please insert password.";
	}
	if (strlen($password) >= 255) {
		return "Password too long.";
	}
	if (!preg_match("/^[a-zA-Z-' 0-9]*$/", $password)) {
		return "Only letters and white space allowed.";
	}
	if ($password !== $confirm_password) {
		return "Passwords do not match.";
	}
	return null;
}

function checkRegistrationData($data, $mysqli)
{
	include('db.php');

	//check if username uses correct characters
	$username = $data["username"];
	$email = $data["email"];
	$password = $data["password"];
	$confirm_password = $data["password_confirmation"];
	$error_message = checkPassword($password, $confirm_password);
	if ($error_message) return $error_message;
	$error_message = checkEmail($email, $mysqli);
	if ($error_message) return $error_message;
	$error_message = checkUsername($username, $mysqli);
	if ($error_message) return $error_message;
	return $error_message;
}

function render_error($status_code, $template, $error_message, $request, $response)
{
	$view = Twig::fromRequest($request);
	$response = $response->withStatus($status_code);
	return $view->render($response, $template, [
		'error_message' => $error_message,
	]);
}
