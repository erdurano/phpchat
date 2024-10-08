<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->get(pattern: "/", callable: function (Request $request, Response $response, $args) {
    $response->getBody()->write(string: "Hello World!");
    return $response;
});

$app->get("/home/", function (Request $request, Response $response, $args) {
    $response->getBody()->write(string: "whatchagonnado");
    return $response;
});


$app->run();
