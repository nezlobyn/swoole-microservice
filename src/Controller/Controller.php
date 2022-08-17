<?php

namespace App\Controller;

use App\Library\AbstractController;
use App\Storage\Storage;

class Controller extends AbstractController
{
    public function query(): void
    {
        $this->jsonResponse(['text' => 'ps']);
    }

    public function create(int $id, string $name): void
    {
        $this->jsonResponse(['id' => $id, 'name' => $name]);
    }
}
