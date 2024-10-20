<?php

namespace App\Controllers;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

trait ControllerTrait
{
    // Provides general methods for interfacing with model
    // Each Controller class must implement their own methods as HTTP method names i.e. 'PUT','POST' etc.  


    protected function defaultErrorResponse(Request $request, Response $response, array $args): Response
    {
        // Creates and returns an error response indicating method is not allowed.
        $err_content["error"] = "This method is not supported for thise endpoint";
        $response = $response->withStatus(code: StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED);
        $response->getBody()->write(string: json_encode(value: $err_content));
        return $response;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $request_method = $request->getMethod();
        if (!in_array(needle: $request_method, haystack: get_class_methods(object_or_class: $this), strict: true)) {
            return $this->defaultErrorResponse($request, $response, $args);
        }

        return $this->$request_method($request, $response, $args);
    }
}
