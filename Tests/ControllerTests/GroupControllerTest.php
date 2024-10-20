<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;

use App\Controllers\GroupController;

use App\Services\GroupService;
use App\Services\ServiceExceptions\GroupAlreadyExists;
use Fig\Http\Message\StatusCodeInterface;

use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertEquals;

final class GroupControllerTest extends TestCase
{
    private RequestFactory $requestFactory;
    private ResponseFactory $responseFactory;
    private $mockService;

    protected function setUp(): void
    {
        $this->requestFactory = new RequestFactory();
        $this->responseFactory = new ResponseFactory();
        $this->mockService = $this->getMockBuilder(GroupService::class)->getMock();
    }

    public function testPostGoodCase(): void
    {
        $this->mockService->method('createGroup')->with('general discussion')->willReturn(
            [
                'id' => 1,
                'group_name' => 'general discussion'
            ]
        );
        $post_request = $this->requestFactory->createRequest('POST', '/');
        $post_request->getBody()->write(
            json_encode(
                [
                    'group_name' => 'general discussion'
                ]
            )
        );
        $testController = new GroupController($this->mockService);
        $response = $this->responseFactory->createResponse();



        $return_response = $testController($post_request, $response, []);

        $return_array = json_decode($return_response->getBody()->__toString(), associative: true);
        $this->assertArrayHasKey('id', $return_array);
        $this->assertArrayHasKey('group_name', $return_array);

        $this->assertEquals(StatusCodeInterface::STATUS_CREATED, $return_response->getStatusCode());
    }

    public function testPostGroupAlreadyExists(): void
    {
        $this->mockService
            ->method('createGroup')
            ->with('general discussion')
            ->willThrowException(
                new GroupAlreadyExists(
                    message: "Group 'general discussion' already exists."
                )
            );
        $test_controller = new GroupController($this->mockService);
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
            [[
                'id' => 'dsdadadas',
                'dsada' => 'dsadasd'
            ]]
        ];
    }

    /**
     * @dataProvider malformedArrayProvider
     */
    public function testPostMalformedBody($malformedData)
    {
        $test_controller = new GroupController($this->mockService);
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

    public function testGetGroups()
    {
        $this->mockService->method('getGroups')->willReturn([
            ['id' => 1, 'group_name' => 'general_discussion'],
            ['id' => 2, 'group_name' => 'music']
        ]);

        $test_controller = new GroupController($this->mockService);
        $post_request = $this->requestFactory->createRequest('GET', '/');
        $post_request;
        $response = $this->responseFactory->createResponse();

        $return_response = $test_controller($post_request, $response, []);

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $return_response->getStatusCode());

        $return_array = json_decode($return_response->getBody()->__toString(), associative: true);

        assertEquals(2, sizeof($return_array));

        foreach ($return_array as $group_info) {
            assertArrayHasKey('id', $group_info);
            assertArrayHasKey('group_name', $group_info);
        }
    }

    public function testGetGroupsWithId()
    {
        $this->mockService->method('getGroupById')
            ->with(1)
            ->willReturn(
                ['id' => 1, 'group_name' => 'general_discussion'],
            );

        $test_controller = new GroupController($this->mockService);
        $post_request = $this->requestFactory->createRequest('GET', '/');
        $post_request;
        $response = $this->responseFactory->createResponse();

        $return_response = $test_controller($post_request, $response, ['id' => 1]);

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $return_response->getStatusCode());

        $return_array = json_decode($return_response->getBody()->__toString(), associative: true);

        assertEquals(4, sizeof($return_array));

        assertArrayHasKey('id', $return_array);
        assertArrayHasKey('group_name', $return_array);
        assertArrayHasKey('members_url', $return_array);
        assertArrayHasKey('messages_url', $return_array);
    }
}
