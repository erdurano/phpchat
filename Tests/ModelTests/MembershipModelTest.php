<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Database\DatabaseConnection;
use App\Models\MembershipModel;
use App\Models\ModelExceptions\ResourceAlreadyExists;
use App\Models\ModelExceptions\ResourceNotFound;

use function PHPUnit\Framework\assertEquals;

define('BASE_DB_PATH', __DIR__ . '/base_db.sqlite3');
define('TEST_DB_PATH', __DIR__ . '/test_db.sqlite3');

final class MembershipModelTest extends TestCase
{
    private DatabaseConnection $testConnection;
    private MembershipModel $testModel;


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
        $this->testModel = new MembershipModel($this->testConnection);
    }

    public function testGetSourceSuccessfull(): void
    {
        assertEquals(1, sizeof($this->testModel->getResource(['group_id' => 1, 'user_id' => 2])));
    }


    public function testGetSingleResourceFails(): void
    {
        $this->expectException(ResourceNotFound::class);
        $this->expectExceptionMessage('User is not a member');
        $this->testModel->getResource(['group_id' => 1, 'user_id' => 1]);
    }

    public function testCreateResourceSuccessfull(): void
    {
        $result_array = $this->testModel->createResource(['group_id' => 1, 'user_id' => 1]);

        $this->assertArrayHasKey(
            'group_id',
            $result_array
        );
        $this->assertArrayHasKey('user_id', $result_array);
    }

    public function testCreateResourceFails(): void
    {
        $this->expectException(ResourceAlreadyExists::class);
        $this->expectExceptionMessage('Membership already exists');
        $this->testModel->createResource(['group_id' => 1, 'user_id' => 2]);
    }
}
