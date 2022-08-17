<?php
declare(strict_types=1);

use App\Application;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Application('swoole-service', '0.0.1');
$app->run();
