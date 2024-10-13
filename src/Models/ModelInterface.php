<?php

namespace App\Models;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


interface ModelInterface
{
    public function handle(Request $request, Response $response, array $args): Response;
}
