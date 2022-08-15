<?php

namespace App\Controller;

use App\Library\AbstractController;

class ExRateController extends AbstractController
{
    public function latestRate(): void
    {
        var_dump(111111111);
        $this->jsonResponse('Hello world');
    }
}
