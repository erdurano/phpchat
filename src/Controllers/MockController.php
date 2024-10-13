<?php

namespace App\Controllers;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controllers\ControllerTrait;


class MockController
{
    use ControllerTrait;


    public function
    POST(Request $request, Response $response, array $args): Response
    {
        // $response->getBody()->write(json_encode(["stream" => "whatever"]));
        // return $response->withStatus(StatusCodeInterface::STATUS_OK);
        return $this->model->handle($request, $response, $args);
    }
}
