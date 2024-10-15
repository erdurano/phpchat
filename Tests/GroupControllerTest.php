<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;
use App\Models\ModelInterface;
use App\Controllers\ControllerTrait as AppControllerTrait;
use App\Controllers\GroupController;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\ModelExceptions\ResourceAlreadyExists;
use Fig\Http\Message\StatusCodeInterface;

final class GroupControllerTest extends TestCase
{
    private RequestFactory $requestFactory;
    private ResponseFactory $responseFactory;
    private MockObject $mockModel;

    protected function setUp(): void
    {
        $this->requestFactory = new RequestFactory();
        $this->responseFactory = new ResponseFactory();
        $this->mockModel = $this->createMock(ModelInterface::class);
    }

    public function testPostGoodCase(): void
    {
        $this->mockModel->method('createResource')->with(['group_name' => 'general discussion'])->willReturn(
            [
                'id' => 1,
                'group_name' => 'general discussion'
            ]
        );
        // $returned_args = $this->mockModel->createResource(['group_name' => 'general discussion']);
        // $this->assertEquals($returned_args, ['id' => 1, 'group_name' => 'general discussion']);
        $test_controller = new GroupController($this->mockModel);
        $post_request = $this->requestFactory->createRequest('POST', '/');
        $post_request->getBody()->write(
            json_encode(
                [
                    'group_name' => 'general discussion'
                ]
            )
        );
        $response = $this->responseFactory->createResponse();


        $return_response = $test_controller($post_request, $response, []);

        $return_array = json_decode($return_response->getBody()->__toString(), associative: true);
        $this->assertArrayHasKey('id', $return_array);
        $this->assertArrayHasKey('group_name', $return_array);
    }

    public function testPostGroupAlreadyExists(): void
    {
        $this->mockModel
            ->method('createResource')
            ->with(['group_name' => 'general discussion'])
            ->willThrowException(
                new ResourceAlreadyExists(
                    message: "Group 'general discussion' already exists."
                )
            );
        $test_controller = new GroupController($this->mockModel);
        $post_request = $this->requestFactory->createRequest('POST', '/');
        $post_request->getBody()->write(
            json_encode(
                [
                    'group_name' => 'general discussion'
                ]
            )
        );
        $response = $this->responseFactory->createResponse();


        $return_response = $test_controller($post_request, $response, []);

        $return_array = json_decode($return_response->getBody()->__toString(), associative: true);
        $this->assertArrayHasKey('error', $return_array);
        $this->assertEquals($return_array['error'], "Group 'general discussion' already exists.");
        $this->assertEquals(StatusCodeInterface::STATUS_CONFLICT, $return_response->getStatusCode());
    }

    public function malformedArrayProvider()
    {
        return [
            [["d1" => 'ddsdsjakldka']],
            [[]],
        ];
    }

    /**
     * @dataProvider malformedArrayProvider
     */
    public function testPostMalformedBody($malformedData)
    {
        $test_controller = new GroupController($this->mockModel);
        $post_request = $this->requestFactory->createRequest('POST', '/');
        $post_request->getBody()->write(
            json_encode($malformedData)
        );
        $response = $this->responseFactory->createResponse();


        $return_response = $test_controller($post_request, $response, []);

        $this->assertEquals(StatusCodeInterface::STATUS_BAD_REQUEST, $return_response->getStatusCode());
        $this->assertEquals(
            [
                'error' => 'Malformed request. Request should have form of:\n' .
                    '[\n\t"group_name": string\n]'
            ],
            json_decode($return_response->getBody()->__toString(), associative: true)
        );
    }
}
