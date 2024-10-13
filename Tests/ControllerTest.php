<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Controllers\ControllerTrait;
use App\Controllers\MockController;
use App\Models\MockModel;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;


final class ControllerTest extends TestCase
{
    private RequestFactory $requestFactory;
    private ResponseFactory $responseFactory;

    protected function setUp(): void
    {
        $this->requestFactory = new RequestFactory();
        $this->responseFactory = new ResponseFactory();
    }

    public function testOfTraitRedirectionToModel(): void
    {
        $test_controller = new MockController(new MockModel());
        $request = $this->requestFactory->createRequest('POST', '/');
        $response = $this->responseFactory->createResponse();

        $returned_response = $test_controller($request, $response, []);

        $this->assertEquals(
            200,
            $returned_response->getStatusCode()
        );
        $this->assertEquals(
            'MockModel',
            json_decode($returned_response->getBody()->__toString())
        );
    }
    public function testTraitRedirectionToDefaultError(): void
    {
        $test_controller = new MockController(new MockModel());
        $request = $this->requestFactory->createRequest('PUT', '/');
        $response = $this->responseFactory->createResponse();

        $returned_response = $test_controller($request, $response, []);

        $this->assertEquals(
            405,
            $returned_response->getStatusCode()
        );
    }
}
