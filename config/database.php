<?php
class Database
{
    private $connection;

    public function getConnection(): PDO
    {
        if ($this->connection instanceof PDO) {
            return $this->connection;
        }

        $config = require __DIR__ . '/config.php';
        $dbConfig = $config['db'];

        $dsn = sprintf(
            '%s:host=%s;port=%s;dbname=%s;charset=%s',
            $dbConfig['driver'],
            $dbConfig['host'],
            $dbConfig['port'],
            $dbConfig['database'],
            $dbConfig['charset']
        );

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $this->connection = new PDO(
            $dsn,
            $dbConfig['username'],
            $dbConfig['password'],
            $options
        );

        if (!empty($dbConfig['collation'])) {
            $this->connection->exec("SET NAMES '{$dbConfig['charset']}' COLLATE '{$dbConfig['collation']}'");
        }

        return $this->connection;
    }
}
