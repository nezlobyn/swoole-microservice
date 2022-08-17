<?php

namespace App\Controller;

use App\{Library\AbstractController, Storage\Storage};

class Controller extends AbstractController
{
    public function query(string $field = null, string $value = null): void
    {
        $storage  = new Storage();

        $this->jsonResponse($storage->getBy($field, $value));
    }

    public function create(string $firstName, string $lastName, string $city): void
    {
        $storage  = new Storage();

        if (!($id = $storage->addEntity($firstName, $lastName, $city))) {
            $this->errorResponse('something went wrong');
        }

        $this->jsonResponse(['id' => $id, 'first_name' => $firstName, 'last_name' => $lastName, 'city' => $city]);
    }
}
