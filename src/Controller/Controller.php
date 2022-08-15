<?php

namespace App\Controller;

use App\Library\AbstractController;

class Controller extends AbstractController
{
    public function query(): void
    {
        $this->jsonResponse('Hello world!!!!!!!!!');
    }
}
