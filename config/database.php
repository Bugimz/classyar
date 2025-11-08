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

        $charset = $dbConfig['charset'] ?? 'utf8mb4';

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $dbConfig['host'],
            $dbConfig['database'],
            $charset
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

        return $this->connection;
    }
}
