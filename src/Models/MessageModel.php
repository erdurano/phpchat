<?php

namespace App\Models;

use App\Database\DatabaseConnection;
use App\Models\ModelExceptions\InvalidArguments;
use App\Models\ModelExceptions\ResourceAlreadyExists;
use App\Models\ModelExceptions\ResourceNotFound;
use PDO;
use PDOException;
use DateTimeImmutable;


class MessageModel implements ModelInterface
{

    public function __construct(private ?DatabaseConnection $connection = null)
    {
        if (is_null($connection)) {
            $this->connection = new DatabaseConnection();
        } else {
            $this->connection = $connection;
        }
    }

    private function buildGetQueryStringAndParams($args): array
    {
        $query_string = 'SELECT * FROM messages';
        $params = [];


        if (!empty($args)) {
            $query_string .= ' where ';
            $clause_tokens = [];

            if (array_key_exists('group_id', $args)) {

                if (is_int($args['group_id'])) {
                    array_push($clause_tokens, 'group_id=:group_id');
                    $params['group_id'] = $args["group_id"];
                    unset($args['group_id']);
                }
            }
            if (array_key_exists('user_id', $args)) {
                if (is_int($args['user_id'])) {
                    array_push($clause_tokens, 'user_id=:user_id');
                    $params['user_id'] = $args["user_id"];
                    unset($args['user_id']);
                }
            }
            if (array_key_exists('since', $args)) {
                if (get_class($args["since"]) == DateTimeImmutable::class) {
                    array_push($clause_tokens, 'created_at>=:since');
                    $params['since'] = date_format($args["since"], 'Y-m-d H:i:s');
                    unset($args['since']);
                }
            }

            if (!empty($args)) {
                throw new InvalidArguments(
                    message: "This method only accepts 'group_id': int, 'user_id': int, 'since': DateTimeImmutable" .
                        " as optional array members."
                );
            }

            $query_string .= implode(" AND ", $clause_tokens);
        }

        $query_string .= " ORDER BY created_at";

        return ['query_string' => $query_string, 'params' => $params];
    }

    public function getResource(array $args = []): array
    {
        $query_and_params = $this->buildGetQueryStringAndParams($args);

        $statement = $this->connection->getConnection()->prepare($query_and_params['query_string']);
        $statement->execute($query_and_params['params']);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function validateCreateArgs(array $args): void
    {
        $args_keys = array_keys($args);
        sort($args_keys);
        $test_keys = ['group_id', 'user_id', 'content'];
        sort($test_keys);

        if ($args_keys == $test_keys) {
            if (
                is_string($args['content']) &&
                is_int($args['user_id']) &&
                is_int($args['group_id'])
            ) {
                return;
            }
        }
        throw new InvalidArguments(
            message: "This method accepts 'group_id':int, 'user_id': int, 'content': string as arguments"
        );
    }


    public function createResource(array $args): array
    {
        $this->validateCreateArgs($args);
        $query_string = 'INSERT INTO messages(group_id, user_id, content) VALUES (:group_id, :user_id, :content) RETURNING *';

        $statement = $this->connection->getConnection()->prepare($query_string);
        $statement->execute($args);
        $result_array = $statement->fetch(PDO::FETCH_ASSOC);

        return $result_array;
    }
}
