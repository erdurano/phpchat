<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Database\DatabaseConnection;
use App\Models\ModelExceptions\ResourceAlreadyExists;
use App\Models\ModelExceptions\ResourceNotFound;
use App\Models\MessageModel;
use App\Models\ModelExceptions\InvalidArguments;

use function PHPUnit\Framework\assertEquals;

define('BASE_DB_PATH', __DIR__ . '/../../chat_try.sqlite3');
define('TEST_DB_PATH', __DIR__ . '/test_db.sqlite3');

final class MessageModelTest extends TestCase
{
    private DatabaseConnection $testConnection;
    private MessageModel $testModel;


    public static function setUpBeforeClass(): void
    {
        copy(BASE_DB_PATH, TEST_DB_PATH);
    }

    public static function tearDownAfterClass(): void
    {
        unlink(TEST_DB_PATH);
    }

    public function setUp(): void
    {
        $this->testConnection = new DatabaseConnection(TEST_DB_PATH);
        $this->testModel = new MessageModel($this->testConnection);
    }

    public function testArgumentSet(): void
    {
        $malordered_array = [
            'user_id' => 1,
            'group_id' => 1,
            'since' => new DateTimeImmutable('2024-10-19 20:20:00')
        ];

        $result = $this->testModel->getResource($malordered_array);

        foreach ($result as $message) {
            $this->assertArrayHasKey('group_id', $message);
            $this->assertArrayHasKey('user_id', $message);
            $this->assertArrayHasKey('content', $message);
            $this->assertArrayHasKey('created_at', $message);
        }
    }

    public function testGetResourceWithLimitedArgumentSet(): void
    {
        $test_args1 = [
            'group_id' => 1,
            'since' => new DateTimeImmutable('2024-10-19 20:20:00')
        ];

        $result = $this->testModel->getResource($test_args1);

        foreach ($result as $message) {
            $this->assertArrayHasKey('group_id', $message);
            $this->assertArrayHasKey('user_id', $message);
            $this->assertArrayHasKey('content', $message);
            $this->assertArrayHasKey('created_at', $message);
        }

        $test_args2 = [
            'group_id' => 1,
        ];
        $result = $this->testModel->getResource($test_args2);

        foreach ($result as $message) {
            $this->assertArrayHasKey('group_id', $message);
            $this->assertArrayHasKey('user_id', $message);
            $this->assertArrayHasKey('content', $message);
            $this->assertArrayHasKey('created_at', $message);
        }
    }

    public function testCreateFailsWithInvalidArgsSet()
    {
        $args = [
            'group_id' => 1,
        ];
        $this->expectException(InvalidArguments::class);
        $this->testModel->createResource($args);
    }
    public function testCreateSuccess()
    {
        $args = [
            'group_id' => 1,
            'user_id' => 1,
            'content' => 'test message'
        ];

        $message = $this->testModel->createResource($args);

        $this->assertArrayHasKey('group_id', $message);
        $this->assertArrayHasKey('user_id', $message);
        $this->assertArrayHasKey('content', $message);
        $this->assertArrayHasKey('created_at', $message);
    }
}
