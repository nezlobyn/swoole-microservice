<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Kernel;
use App\Storage\Storage;
use Doctrine\DBAL\DriverManager;
use Swoole\HTTP\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Doctrine\DBAL\Exception;

class run
{
    public const HOST = '0.0.0.0';
    public const PORT = '6000';

    /**
     * @var Kernel
     */
    private $kernel;

    public function __construct()
    {
        $server = new Server($this::HOST, $_ENV['SWOOLE_PORT'] ?? $this::PORT);

        $server->on("Start", function() {
            echo sprintf("Swoole HTTP server is started at http://%s:%s\n", $this::HOST, $_ENV['SWOOLE_PORT'] ?? $this::PORT);
        });

        $server->on('request', [$this, 'onRequest']);

        $server->start();
    }

    public function onRequest(Request $request, Response $response): void
    {
        // Create Kernel
        if (!$this->kernel) {
            $this->kernel = new Kernel();
        }

        // Process Request
        $this->kernel->boot($request, $response);
    }
}

new run();
