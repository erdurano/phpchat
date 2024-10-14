<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\GroupService;
use App\Models\ModelInterface;
use App\Models\UserModel;

use function PHPUnit\Framework\isEmpty;

class GroupController
{
    use ControllerTrait;

    public function POST(Request $request, Response $response, array $args)
    {
        $returned_data = $this->model->createResource($args);

        if (!empty($returned_data)) {
            $response->getBody()->write(json_encode($returned_data));
            return $response;
        };
    }
}
