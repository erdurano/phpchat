<?php

namespace App\Middlewares;

use Slim\App;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class ContentTypeMiddleware
{
    /*
    A middleware layer controlling incoming requests for ContentType
    header value and passes only requests with "application/json".
    If header is anything other, returns a 415: Unsopported Media Type
    */

    protected App $app;
    protected string $required_type = 'application/json';
    function __construct(App $app)
    {
        $this->app = $app;
    }

    function __invoke(Request $request, RequestHandler $handler): Response
    {
        if ($request->getHeaderLine(name: 'Content-Type') != "application/json") {

            $content['error'] = 'Endpoints only accepts \'application/json\' as content type.' .
                ' Your request supplied: ' . $request->getHeaderLine('Content-Type');

            $response = $this->app->getResponseFactory()->createResponse(415);
            $response->getBody()->write(json_encode(value: $content));

            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response = $handler->handle($request);

            // After other middlewares, while returning the response, checks and corrects return Content-Type
            if ($response->getHeaderLine('Content-Type') != $this->required_type) {
                $response = $response->withHeader(name: 'Content-Type', value: 'application/json');
            }
            return $response;
        }
    }
}
