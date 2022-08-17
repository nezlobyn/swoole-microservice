<?php
declare(strict_types=1);

use App\Application;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Application('LMP Comments', '0.0.1');
$app->run();
