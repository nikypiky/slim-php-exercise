<?php

namespace App\Middleware;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Response as SlimResponse;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

class CheckLoginMiddleware
{
    public function __invoke($request, $handler): Response
    {
	$view = Twig::fromRequest($request);

	//reroute user if not logged in
	if(!isset($_SESSION["login_status"]) || $_SESSION["login_status"] !== true) {
        $response = new SlimResponse();
        $response = $response->withStatus(401);
        return $view->render($response, 'unauthorised-page.html.twig');
	}

    // Proceed with the next middleware
    return $handler->handle($request);
    }
}
