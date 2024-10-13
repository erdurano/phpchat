<?php

namespace App\Middlewares;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Response;

class ContentTypeMiddleware
{
    /*
    A middleware layer controlling incoming requests for ContentType
    header value and passes only requests with "application/json".
    If header is anything other, returns a 415: Unsopported Media Type
    */
    private string $errorContentTemplate = 'Endpoints only accepts \'application/json\' as content type.' .
        ' Your request supplied: %s';

    protected ResponseFactory $response_factory;
    protected string $required_type = 'application/json';


    function __construct()
    {
        $this->response_factory = new ResponseFactory();
    }

    private function getErrorContent(Request $request): array
    {
        return [
            'error' => sprintf(
                $this->errorContentTemplate,
                $request->getHeaderLine('Content-Type')
            )
        ];
    }

    function __invoke(Request $request, RequestHandler $handler): Response
    {
        if ($request->getHeaderLine(name: 'Content-Type') != "application/json") {

            // If content-Type anythong but 'application/json' returns response with 415
            $response = $this->response_factory->createResponse(StatusCodeInterface::STATUS_UNSUPPORTED_MEDIA_TYPE);
            $response->getBody()->write(json_encode(value: $this->getErrorContent($request)));

            return $response->withHeader('Content-Type', 'application/json');
        } else {
            //Adding  'Accept: application/json' to request header for getting json error texts.
            $request->withAddedHeader('Accept', 'application/json');

            $response = $handler->handle($request);

            // After other middlewares, while returning the response, checks and corrects return Content-Type
            if ($response->getHeaderLine('Content-Type') != $this->required_type) {
                $response = $response->withHeader(name: 'Content-Type', value: 'application/json');
            }
            return $response;
        }
    }
}
