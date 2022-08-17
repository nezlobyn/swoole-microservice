<?php
declare(strict_types=1);

namespace App;

use Symfony\Component\Console\{
    Input\InputInterface,
    Output\OutputInterface
};
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use Symfony\Component\Console\Application as ConsoleApplication;

class Application extends ConsoleApplication
{
    public const HOST = '0.0.0.0';
    public const PORT = 6000;

    /**
     * @var Kernel
     */
    private $kernel;

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $server = new Server($_ENV['SWOOLE_HOST'] ?? $this::HOST, (int)$_ENV['SWOOLE_PORT'] ?? $this::PORT);
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
