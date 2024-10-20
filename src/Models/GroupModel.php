<?php

namespace App\Models;

use App\Database\DatabaseConnection;
use App\Models\ModelExceptions\InvalidArguments;
use App\Models\ModelExceptions\ResourceAlreadyExists;
use App\Models\ModelExceptions\ResourceNotFound;
use PDO;
use PDOException;

class GroupModel implements ModelInterface
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
        $query_string = 'SELECT * FROM groups';
        $params = [];
        $id = null;

        if (!empty($args)) {

            if (array_keys($args) != ['id']) {
                throw new InvalidArguments(message: "This method only accepts 'id':int as argument");
            } else {
                $id = $args['id'];
                $query_string .= ' WHERE id=:group_id';
                $params['group_id'] = $id;
            }
        }

        $statement = $this->connection->getConnection()->prepare($query_string);
        $statement->execute($params);

        $query_result = $statement->fetchAll(PDO::FETCH_ASSOC);
        if (sizeof($query_result) == 0) {
            throw new ResourceNotFound(message: sprintf('Group with id=%s not found.', $id));
        } elseif (sizeof($query_result) == 1) {
            $return_array = $query_result[0];
        } else {
            $return_array = $query_result;
        };
        return $return_array;
    }
    public function createResource(array $args): array
    {
        if (array_keys($args) != ["group_name"]) {
            throw new InvalidArguments(message: 'This methd only accepts "group_name":string as argument array member');
        }

        $query_string = "INSERT INTO groups (group_name) VALUES (:group_name) RETURNING *";

        $statement = $this->connection->getConnection()->prepare($query_string);
        try {
            $statement->execute($args);
            $return_array = $statement->fetch(PDO::FETCH_ASSOC);
            return $return_array;
        } catch (PDOException $e) {
            throw new ResourceAlreadyExists(message: sprintf('A group with name "%s" already exists', $args["group_name"]));
        }
    }
}
