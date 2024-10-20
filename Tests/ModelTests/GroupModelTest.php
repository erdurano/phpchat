<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Database\DatabaseConnection;
use App\Models\GroupModel;
use App\Models\ModelExceptions\ResourceAlreadyExists;
use App\Models\ModelExceptions\ResourceNotFound;

use function PHPUnit\Framework\assertEquals;

define('BASE_DB_PATH', __DIR__ . '/../../chat_try.sqlite3');
define('TEST_DB_PATH', __DIR__ . '/test_db.sqlite3');

final class GroupModelTest extends TestCase
{
    private DatabaseConnection $testConnection;
    private GroupModel $testModel;


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
        $this->testModel = new GroupModel($this->testConnection);
    }

    public function testGetSourceSuccessfull(): void
    {
        assertEquals($this->testModel->getResource(1), [
            'id' => 1,
            'group_name' => 'general discussion'
        ]);
    }

    public function testGetResourceAsList(): void
    {
        assertEquals(
            [
                [
                    'id' => 1,
                    'group_name' => 'general discussion'
                ],
                [
                    'id' => 2,
                    'group_name' => 'music'
                ]
            ],
            $this->testModel->getResource()
        );
    }

    public function testGetSingleResourceFails(): void
    {
        $this->expectException(ResourceNotFound::class);
        $this->expectExceptionMessage('Group with id=5 not found.');
        $this->testModel->getResource(id: 5);
    }

    public function testCreateResourceSuccessfull(): void
    {
        $result_array = $this->testModel->createResource(['group_name' => 'test group']);

        $this->assertArrayHasKey(
            'id',
            $result_array
        );
        $this->assertArrayHasKey('group_name', $result_array);
        $this->assertEquals('test group', $result_array['group_name']);
    }

    public function testCreateResourceFails(): void
    {
        $this->expectException(ResourceAlreadyExists::class);
        $this->expectExceptionMessage('A group with name "general discussion" already exists');
        $this->testModel->createResource(['group_name' => 'general discussion']);
    }
}
