<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Database\DatabaseConnection;
use App\Models\ModelExceptions\ResourceAlreadyExists;
use App\Models\ModelExceptions\ResourceNotFound;
use App\Models\UserModel;

use function PHPUnit\Framework\assertEquals;

define('BASE_DB_PATH', __DIR__ . '/../../chat_try.sqlite3');
define('TEST_DB_PATH', __DIR__ . '/test_db.sqlite3');

final class UserModelTest extends TestCase
{
    private DatabaseConnection $testConnection;
    private UserModel $testModel;


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
        $this->testModel = new UserModel($this->testConnection);
    }

    public function testGetSourceSuccessfull(): void
    {
        assertEquals($this->testModel->getResource(['id' => 1]), [
            'id' => 1,
            'username' => 'erdurano'
        ]);
    }

    public function testGetResourceAsList(): void
    {
        assertEquals(
            [
                [
                    'id' => 1,
                    'username' => 'erdurano'
                ],
                [
                    'id' => 2,
                    'username' => 'setnay'
                ]
            ],
            $this->testModel->getResource()
        );
    }

    public function testGetSingleResourceFails(): void
    {
        $this->expectException(ResourceNotFound::class);
        $this->expectExceptionMessage('User with id=5 not found.');
        $this->testModel->getResource(['id' => 5]);
    }

    public function testCreateResourceSuccessfull(): void
    {
        $result_array = $this->testModel->createResource(['username' => 'test user']);

        $this->assertArrayHasKey(
            'id',
            $result_array
        );
        $this->assertArrayHasKey('username', $result_array);
        $this->assertEquals('test user', $result_array['username']);
    }

    public function testCreateResourceFails(): void
    {
        $this->expectException(ResourceAlreadyExists::class);
        $this->expectExceptionMessage('A user with name "erdurano" already exists');
        $this->testModel->createResource(['username' => 'erdurano']);
    }
}
