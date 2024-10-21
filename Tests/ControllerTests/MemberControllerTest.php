<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;
use App\Models\ModelInterface;
use App\Controllers\MembersController;
use App\Services\MemberService;
use App\Services\ServiceExceptions\AlreadyMember;
use Fig\Http\Message\StatusCodeInterface;

use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertEquals;

final class MemberControllerTest extends TestCase
{
    private RequestFactory $requestFactory;
    private ResponseFactory $responseFactory;
    private $mockService;

    protected function setUp(): void
    {
        $this->requestFactory = new RequestFactory();
        $this->responseFactory = new ResponseFactory();
        $this->mockService = $this->createMock(MemberService::class);
    }

    public function testPostGoodCase(): void
    {
        $this->mockService->method('subscribeUserToGroup')->with('erdurano', 1)->willReturn(
            [
                'group_id' => 1,
                'group_name' => 'general discussion',
                'members' => [
                    'user_id' => 1,
                    'user_name' => 'erdurano'
                ]
            ]
        );
        $test_controller = new MembersController($this->mockService);
        $post_request = $this->requestFactory->createRequest('POST', '/groups/1/members');
        $post_request->getBody()->write(
            json_encode(
                [
                    'user_name' => 'erdurano'
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
            'members',
            $return_array
        );
        $this->assertArrayHasKey('members', $return_array);
        $this->assertArrayHasKey('user_id', $return_array['members']);
        $this->assertArrayHasKey('user_name', $return_array['members']);
        $this->assertEquals(StatusCodeInterface::STATUS_CREATED, $return_response->getStatusCode());
    }

    public function testPostMemberAlreadyExists(): void
    {
        $this->mockService
            ->method('subscribeUserToGroup')
            ->with(
                'setnay',
                1
            )
            ->willThrowException(
                new AlreadyMember(
                    message: "'setnay' is already member of 'general discussion'."
                )
            );
        $test_controller = new MembersController($this->mockService);
        $post_request = $this->requestFactory->createRequest('POST', '/groups/1/members');
        $post_request->getBody()->write(
            json_encode(
                [
                    'user_name' => 'setnay'
                ]
            )
        );
        $response = $this->responseFactory->createResponse();


        $return_response = $test_controller($post_request, $response, ['group_id' => 1]);

        $return_array = json_decode($return_response->getBody()->__toString(), associative: true);
        $this->assertArrayHasKey('error', $return_array);
        $this->assertEquals($return_array['error'], "'setnay' is already member of 'general discussion'.");
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
        $test_controller = new MembersController($this->mockService);
        $post_request = $this->requestFactory->createRequest('POST', '/groups/1/members');
        $post_request->getBody()->write(
            json_encode($malformedData)
        );
        $response = $this->responseFactory->createResponse();


        $return_response = $test_controller($post_request, $response, ['id' => 1]);

        $this->assertEquals(StatusCodeInterface::STATUS_BAD_REQUEST, $return_response->getStatusCode());
        $this->assertEquals(
            [
                'error' => 'Malformed request. Request should have form of:\n' .
                    '[\n\t"user_name": string\n]'
            ],
            json_decode($return_response->getBody()->__toString(), associative: true)
        );
    }

    public function testGetMembers()
    {
        $this->mockService->method('getMembersByGroupId')->with(1)->willReturn([

            'id' => 1,
            'group_name' => 'general_discussion',
            'members' => [
                [
                    'id' => 1,
                    'user_name' => 'erdurano'
                ],
                [
                    'id' => 2,
                    'user_name' => 'setnay'
                ]
            ]

        ]);

        $test_controller = new MembersController($this->mockService);
        $post_request = $this->requestFactory->createRequest('GET', '/groups/1/members');
        $post_request;
        $response = $this->responseFactory->createResponse();

        $return_response = $test_controller($post_request, $response, ['group_id' => 1]);

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $return_response->getStatusCode());

        $return_array = json_decode($return_response->getBody()->__toString(), associative: true);

        assertEquals(4, sizeof($return_array));

        assertArrayHasKey('messages_url', $return_array);

        foreach ($return_array['members'] as $member_info) {
            assertArrayHasKey('id', $member_info);
            assertArrayHasKey('user_name', $member_info);
        }
    }
}
