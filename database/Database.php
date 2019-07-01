<?php

namespace App\Database;

use \App\Database\DatabaseConfig;

class Database {
    private static $instance;
    private $connection;

    private function __construct(DatabaseConfig $dbConfig) {
        $this->connection = new \mysqli(
            $dbConfig->host,
            $dbConfig->username,
            $dbConfig->password,
            $dbConfig->name,
            $dbConfig->port
        );

        if ($this->connection->connect_error) {
            throw new \Exception(
                $connection->connect_error,
                $connection->connect_errno
            );
        }
    }

    static function getInstance() {
        $config = new DatabaseConfig(
            CONFIG['db']['host'],
            CONFIG['db']['username'],
            CONFIG['db']['password'],
            CONFIG['db']['name'],
            CONFIG['db']['port']
        );

        if (is_null(self::$instance)) {
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    function startTransaction() {
        return $this->rawQuery("START TRANSACTION");
    }

    function commit() {
        return $this->rawQuery("COMMIT");
    }

    function rollback() {
        return $this->rawQuery("ROLLBACK");
    }

    function query($sql, $params = []) {
        if ($stmt = $this->connection->prepare($sql)) {
            if (!is_array($params)) {
                throw new \Exception('Invalid array of sql query params');
            }

            if (!empty($params)) {
                $bindTypes = '';
                foreach ($params as $param) {
                    switch(gettype($param)) {
                        case 'integer':
                        case 'boolean':
                            $bindTypes .= 'i';
                            break;
                        case 'double':
                            $bindTypes .= 'd';
                            break;
                        case 'string':
                        default:
                            $bindTypes .= 's';
                            break;
                    }
                }
                $stmt->bind_param($bindTypes, ...$params);
            }

            
            if (!$stmt->execute()) {
                throw new \Exception($stmt->error);
            }

            $result = $stmt->get_result();
            
            if (is_bool($result)) {
                $result = null;
            } else {
                $result = $result->fetch_all(MYSQLI_ASSOC);
            }

            $stmt->close();

            return $result;
        } else {
            throw new \Exception('Error preparing statement to query database');
        }
    }

    function rawQuery($sql) {
        $executed = $this->connection->query($sql);

        if (!$executed) {
            throw new \Exception("Cannot execute query");
        }

        return $executed;
    }

    function lastInsertedId() {
        return $this->rawQuery('SELECT LAST_INSERT_ID()')->fetch_row()[0];
    }

    function __destruct() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}
