<?php

namespace App\Models;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


interface ModelInterface
{
    public function getResource(array $args): array;
    public function createResource(array $args): array;
}
