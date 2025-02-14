<?php

namespace App\Middleware;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Response as SlimResponse;

class CheckLoginMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {

	//reroute user if not logged in
	if(!isset($_SESSION["login_status"]) || $_SESSION["login_status"] !== true) {
        $response = new SlimResponse();
		return $response->withHeader('Location', '/')->withStatus(302);
	}

    // Proceed with the next middleware
    return $handler->handle($request);
    }
}
