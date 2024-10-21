<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;
use App\Models\ModelInterface;
use App\Controllers\MessageController;
use PHPUnit\Framework\MockObject\MockObject;
use App\Models\ModelExceptions\ResourceAlreadyExists;
use App\Services\MemberService;
use App\Services\MessageService;
use App\Services\ServiceExceptions\AlreadyMember;
use Fig\Http\Message\StatusCodeInterface;

use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertEquals;

final class MessageControllerTest extends TestCase
{
    private RequestFactory $requestFactory;
    private ResponseFactory $responseFactory;
    private $mockService;

    protected function setUp(): void
    {
        $this->requestFactory = new RequestFactory();
        $this->responseFactory = new ResponseFactory();
        $this->mockService = $this->createMock(MessageService::class);
    }

    public function testPostGoodCase(): void
    {
        $this->mockService->method('sendMessage')->with(1, 'erdurano', 'test message')->willReturn(
            [
                'group_id' => 1,
                'group_name' => 'general discussion',
                'messages' => [


                    [
                        'sender' =>
                        [
                            'user_id' => 1,
                            'user_name' => 'erdurano'
                        ],
                        'content' => 'test message',
                        'created_at' => '2024-12-10 12:45:13'
                    ]

                ]
            ]
        );
        $test_controller = new MessageController($this->mockService);
        $post_request = $this->requestFactory->createRequest('POST', '/groups/1/messages');
        $post_request->getBody()->write(
            json_encode(
                [
                    'user_name' => 'erdurano',
                    'message' => 'test message'
                ]
            )
        );
        $response = $this->responseFactory->createResponse();


        $return_response = $test_controller($post_request, $response, ['group_id' => 1]);

        $return_array = json_decode($return_response->getBody()->__toString(), associative: true);
        $this->assertArrayHasKey('group_id', $return_array);
        $this->assertArrayHasKey(
            'group_name',
            $return_array
        );
        $this->assertArrayHasKey(
            'messages',
            $return_array
        );
        $this->assertArrayHasKey('sender', $return_array['messages'][0]);
        $this->assertArrayHasKey('created_at', $return_array['messages'][0]);
        $this->assertArrayHasKey('content', $return_array['messages'][0]);
        $this->assertArrayHasKey('user_id', $return_array['messages'][0]['sender']);
        $this->assertArrayHasKey('user_name', $return_array['messages'][0]['sender']);;
        $this->assertEquals(StatusCodeInterface::STATUS_CREATED, $return_response->getStatusCode());
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
        $test_controller = new MessageController($this->mockService);
        $post_request = $this->requestFactory->createRequest('POST', '/groups/1/messages');
        $post_request->getBody()->write(
            json_encode($malformedData)
        );
        $response = $this->responseFactory->createResponse();


        $return_response = $test_controller($post_request, $response, ['group_id' => 1]);

        $this->assertEquals(StatusCodeInterface::STATUS_BAD_REQUEST, $return_response->getStatusCode());
        $this->assertEquals(
            [
                'error' => 'Malformed request. Request',
                'request_schema' =>
                [
                    "user_name" => 'string type',
                    'message' => 'string type'
                ]

            ],
            json_decode($return_response->getBody()->__toString(), associative: true)
        );
    }

    public function testGetMessage()
    {
        $this->mockService->method('listMessages')->with(1)->willReturn(
            [
                'group_id' => 1,
                'group_name' => 'general discussion',
                'messages' => [


                    [
                        'sender' =>
                        [
                            'user_id' => 1,
                            'user_name' => 'erdurano'
                        ],
                        'content' => 'test message',
                        'created_at' => '2024-12-10 12:45:13'
                    ],
                    [
                        'sender' =>
                        [
                            'user_id' => 1,
                            'user_name' => 'erdurano'
                        ],
                        'content' => 'test message2',
                        'created_at' => '2024-12-10 13:45:13'
                    ]

                ]
            ]
        );

        $test_controller = new MessageController($this->mockService);
        $post_request = $this->requestFactory->createRequest('GET', '/groups/1/messages');
        $post_request;
        $response = $this->responseFactory->createResponse();

        $return_response = $test_controller($post_request, $response, ['group_id' => 1]);

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $return_response->getStatusCode());

        $return_array = json_decode($return_response->getBody()->__toString(), associative: true);

        assertEquals(3, sizeof($return_array));
        $this->assertArrayHasKey('sender', $return_array['messages'][0]);
        $this->assertArrayHasKey('created_at', $return_array['messages'][0]);
        $this->assertArrayHasKey('content', $return_array['messages'][0]);
        $this->assertArrayHasKey('user_id', $return_array['messages'][0]['sender']);
        $this->assertArrayHasKey('user_name', $return_array['messages'][0]['sender']);
        $this->assertEquals(2, sizeof($return_array['messages']));
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $return_response->getStatusCode());
    }
}
