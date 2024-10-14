<?php

namespace App\Models;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


class UserModel implements ModelInterface
{
    private array $content = [

        ['id' => '1',
        'username' => 'erdurano']
    ];

    public function getResource(array $args): array {
        for (this->)
    }
    public function createResource(array $args): array {}
}
