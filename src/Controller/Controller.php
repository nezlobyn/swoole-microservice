<?php

namespace App\Controller;

use App\Library\AbstractController;

class Controller extends AbstractController
{
    public function query(): void
    {
        $this->jsonResponse(['text' => 'Hello world!']);
    }

    public function create(int $id, string $name): void
    {
        $this->jsonResponse(['id' => $id, 'name' => $name]);
    }
}
