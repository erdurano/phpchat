<?php

namespace App\Models;

use App\Database\DatabaseConnection;
use App\Models\ModelExceptions\InvalidArguments;
use App\Models\ModelExceptions\ResourceAlreadyExists;
use App\Models\ModelExceptions\ResourceNotFound;
use PDO;
use PDOException;

class MembershipModel implements ModelInterface
{

    public function __construct(private ?DatabaseConnection $connection = null)
    {
        if (is_null($connection)) {
            $this->connection = new DatabaseConnection();
        } else {
            $this->connection = $connection;
        }
    }
    public function getResource(array $args = []): array
    {
        $query_string = 'SELECT * FROM groups_users WHERE group_id=:group_id AND user_id=:user_id';

        if (!empty($args)) {

            if (array_keys($args) != ['group_id', 'user_id']) {
                throw new InvalidArguments(message: "This method accepts 'group_id':int and 'user_id':int as argument array members");
            }
        }

        $statement = $this->connection->getConnection()->prepare($query_string);
        $statement->execute($args);

        $query_result = $statement->fetchAll(PDO::FETCH_ASSOC);
        if (sizeof($query_result) == 0) {
            throw new ResourceNotFound(message: 'User is not a member');
        } elseif (sizeof($query_result) == 1) {
            $return_array = $query_result[0];

            return $return_array;
        }
    }

    public function createResource(array $args): array
    {
        if (array_keys($args) != ["group_id", "user_id"]) {
            throw new InvalidArguments(message: 'This method only and only accepts "group_id":int and "user_id":int as argument array members');
        }

        $query_string = "INSERT INTO groups_users (group_id, user_id) VALUES (:group_id, :user_id) RETURNING *";

        $statement = $this->connection->getConnection()->prepare($query_string);
        try {
            $statement->execute($args);
            $return_array = $statement->fetch(PDO::FETCH_ASSOC);
            return $return_array;
        } catch (PDOException $e) {
            throw new ResourceAlreadyExists(message: 'Membership already exists');
        }
    }
}
