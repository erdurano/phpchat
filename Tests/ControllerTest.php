<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;
use App\Models\ModelInterface;
use App\Controllers\ControllerTrait as AppControllerTrait;
use App\Controllers\GroupController;
use PHPUnit\Framework\MockObject\MockObject;


final class ControllerTest extends TestCase
{
    private RequestFactory $requestFactory;
    private ResponseFactory $responseFactory;
    private MockObject $mockModel;

    protected function setUp(): void
    {
        $this->requestFactory = new RequestFactory();
        $this->responseFactory = new ResponseFactory();
        $this->mockModel = $this->getMockBuilder(ModelInterface::class)->getMock();
    }

    public function testTraitRedirectionToDefaultError(): void
    {
        $test_controller = $this->getMockForTrait(AppControllerTrait::class, callOriginalConstructor: false);
        $test_controller->model = $this->mockModel;
        $request = $this->requestFactory->createRequest('GET', '/');
        $response = $this->responseFactory->createResponse();

        $returned_response = $test_controller($request, $response, []);

        $this->assertEquals(
            expected: 405,
            actual: $returned_response->getStatusCode()
        );
    }

    public function testGroupControllerPostGoodCase(): void
    {
        $this->mockModel->method('createResource')->with(['name' => 'general discussion'])->willReturn(
            [
                'id' => 1,
                'group_name' => 'general discussion'
            ]
        );
        $test_controller = new GroupController($this->mockModel);
        $post_request = $this->requestFactory->createRequest('POST', '/');
        $post_request->getBody()->write(json_encode([
            'group_name' => 'general discussion'
        ]));
        $response = $this->responseFactory->createResponse();


        $return_response = $test_controller($post_request, $response, []);

        $return_array = json_decode($return_response->getBody()->__toString());
        $this->assertArrayHasKey('id', $return_array);
        $this->assertArrayHasKey('group_name', $return_array);
    }
}
