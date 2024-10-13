<?php

namespace App\Models;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\ModelInterface;


class MockModel implements ModelInterface
{
    public function handle(Request $request, Response $response, array $args): Response
    {
        $response->getBody()->write(json_encode("MockModel"));
        return $response->withStatus(200);
    }
}
