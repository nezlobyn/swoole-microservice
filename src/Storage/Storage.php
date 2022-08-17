<?php

namespace App\Storage;

use Doctrine\DBAL\{Connection, DriverManager};

class Storage
{
    protected string $tableName = 'persons';

    /**
     * @var Connection
     */
    protected $connection;

    public function __construct()
    {
        $connectionParams = [
            'dbname' => $_ENV['DB_NAME'],
            'user' => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASSWORD'],
            'host' => $_ENV['DB_HOST'],
            'driver' => 'pdo_mysql',
        ];
        $this->connection = DriverManager::getConnection($connectionParams);
        $this->createTable();
    }

    public function getBy(string $field = null, string $value = null): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($this->tableName);

        if (null != $field) {
            $qb->where("$field = \"$value\"");
        }

        return $qb->executeQuery()->fetchAssociative();
    }

    public function createTable(): void
    {
        $this->connection->executeQuery("CREATE TABLE IF NOT EXISTS {$this->tableName} (
            id int NOT NULL AUTO_INCREMENT,
            last_name varchar(255),
            first_name varchar(255),
            city varchar(255),
            PRIMARY KEY (id)
        )");
    }

    public function addEntity(string $firstName, string $lastName, string $city): ?int
    {
        $this->connection->createQueryBuilder()
            ->insert($this->tableName)
            ->values(['first_name' => '?', 'last_name' => '?', 'city' => '?'])
            ->setParameter(0, $firstName)
            ->setparameter(1, $lastName)
            ->setparameter(2, $city)
            ->executeQuery();

        return (int)$this->connection->lastInsertId();
    }
}
