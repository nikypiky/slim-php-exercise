<?php
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\Middleware\CheckLoginMiddleware;

$app->get('/edit-user-page/{id}', function ($request, $response, array $args){
	$view = Twig::fromRequest($request);
	$id = $args['id'];

	return $view->render($response, 'edit-user-page.html.twig', [
		'id' => $id,
	]);
})->add(new CheckLoginMiddleware());
