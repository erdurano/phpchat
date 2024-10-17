<?php

namespace App\Controllers;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\ModelExceptions\ResourceAlreadyExists;



class GroupController
{
    use ControllerTrait;

    public function POST(Request $request, Response $response, array $args): Response
    {

        $requestData = json_decode($request->getBody()->__toString(), associative: true);
        try {
            if (array_keys($requestData) != ['group_name']) {
                $response = $response->withStatus(StatusCodeInterface::STATUS_BAD_REQUEST);
                $response->getBody()->write(
                    json_encode(
                        [
                            'error' => 'Malformed request. Request should have form of:\n' .
                                '[\n\t"group_name": string\n]'
                        ]
                    )
                );
                return $response;
            }

            $returned_data = $this->model->createResource($requestData);
            if (!empty($returned_data)) {
                $response = $response->withStatus(StatusCodeInterface::STATUS_CREATED);
                $response->getBody()->write(json_encode($returned_data));
            };
        } catch (ResourceAlreadyExists $e) {

            $error_message = ['error' => $e->getMessage()];
            $response = $response->withStatus(StatusCodeInterface::STATUS_CONFLICT);
            $response->getBody()->write(json_encode($error_message));
        }
        return $response;
    }

    public function GET(Request $request, Response $response, array $args): Response
    {
        if (!empty($args)) {
            $extra_params = array_diff(['id'], array_keys($args));
            if (!empty($extra_params)) {
                return $response->withStatus(StatusCodeInterface::STATUS_BAD_REQUEST)
                    ->getBody()
                    ->write(
                        json_encode([
                            'error' => sprintf(
                                "Invalid query parameters. %s not allowed as query parameters.",
                                implode(", ", $extra_params)
                            )
                        ])
                    );
            }

            // Check what kind of json i should return for singular resource.
            $returned_data = $this->model->getResource($args);
        } else {
            $returned_data = $this->model->getResource([]);
        }

        $response->getBody()->write(json_encode($returned_data));

        return $response;
    }
}
