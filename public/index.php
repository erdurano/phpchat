<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../src/models.php';

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$model = new MyModel();

$model_text = $model->get_text();

$app->get(pattern: "/", callable: function (Request $request, Response $response, $args) {
    $response->getBody()->write(string: "Hello World!");
    return $response;
});

$app->get("/home/", function (Request $request, Response $response, $args) {
    $response->getBody()->write(string: $GLOBALS["model"]->get_text());
    return $response;
});


$app->run();
