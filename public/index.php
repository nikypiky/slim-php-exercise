<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello world!");
    return $response;
});

// Define app routes
$app->get('/hello', function (Request $request, Response $response, $args) {
    include("../src/db.php");
    $sql = 'SELECT * FROM customers';
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    mysqli_close($conn);
    // echo $row["id"] . "<br>";
    $response->getBody()->write("Customer I: " . $row["id"]);
    return $response;
});

$app->run();
