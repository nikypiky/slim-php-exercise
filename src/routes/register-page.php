<?php

use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

$app->get('/register-page', function ($request, $response) {
	$view = Twig::fromRequest($request);
	return $view->render($response, 'register-page.html.twig');
});
