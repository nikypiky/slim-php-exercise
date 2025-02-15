<?php

use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

$app->get('/', function ($request, $response) {
	$view = Twig::fromRequest($request);
	if(isset($_SESSION["login_status"]) && $_SESSION["login_status"] == true) {
		return $response->withheader('location', '/user-table')->withstatus(302);
	} else {
		return $view->render($response, 'login-page.html.twig');
	}
});
