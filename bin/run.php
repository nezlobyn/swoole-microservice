<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Kernel;
use Swoole\HTTP\Server;
use Swoole\HTTP\Request;
use Swoole\HTTP\Response;

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
        $server = new Server($_ENV['SWOOLE_HOST'] ?? $this::HOST, $_ENV['SWOOLE_PORT'] ?? $this::PORT);
        $server->on("Start", function() {
            echo sprintf("Swoole HTTP server is started at http://%s:%s\n", $_ENV['SWOOLE_HOST'] ?? $this::HOST, $_ENV['SWOOLE_PORT'] ?? $this::PORT);
        });

        $server->on('request', [$this, 'onRequest']);
        $server->start();
    }

    public function onRequest(Request $request, Response $response): void
    {
        if (!$this->kernel) {
            $this->kernel = new Kernel();
        }

        $this->kernel->boot($request, $response);
    }
}

new run();
