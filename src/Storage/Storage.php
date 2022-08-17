<?php

namespace App\Storage;

use Doctrine\DBAL\Connection;

class Storage
{
    protected Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function get()
    {
        return $this->connection->getDatabase();
    }
}
