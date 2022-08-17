<?php

namespace App;

use App\Library\Http\{ArgumentResolver, Helper, Request, ResponseErrorTrait};
use FastRoute\{
    DataGenerator\GroupCountBased,
    Dispatcher,
    Dispatcher\GroupCountBased as GroupCountBasedDispatcher,
    RouteCollector,
    RouteParser\Std
};
use Nyholm\Psr7\UploadedFile;
use Psr\Http\Message\{RequestInterface, ServerRequestInterface, UploadedFileInterface};
use Swoole\Http\{Request as SwooleRequest, Response};

class Kernel
{
    use ResponseErrorTrait;

    private array $container = [];

    /**
     * @var Dispatcher
     */
    private $router;

    public function __construct()
    {
        if (!$this->router) {
            // Load Routes
            $routes = \yaml_parse_file(Helper::getRootDir('config/routes.yml'));

            // Create Routes
            $collector = new RouteCollector(new Std(), new GroupCountBased());
            foreach ($routes as $route) {
                $collector->addRoute($route['method'], $route['path'], str_replace('/', '\\', $route['controller']));
            }
            $this->router = new GroupCountBasedDispatcher($collector->getData());
        }
    }

    public function boot(SwooleRequest $request, Response $response): void
    {
        $route = $this->router->dispatch($request->server['request_method'], $request->server['request_uri']);

        switch ($route[0]) {
            case Dispatcher::NOT_FOUND:
                $this->errorResponse($response);
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $this->errorResponse($response, 'Method Not Allowed!', 405);
                break;
            case Dispatcher::FOUND:
                [$class, $method] = explode('::', $route[1]);

                $this->callController($this->getRequest($request), $response, $class, $method);
                break;
        }
    }

    private function callController(Request $request, Response $response, $class, $method): void
    {
        if (!isset($this->container[$class])) {
            $this->container[$class] = new $class();
        }

        $this->container[$class]->set($request, $response);
        $this->container[$class]->{$method}(...(new ArgumentResolver)->getArguments($request, $response, $class, $method));
    }

    protected function getRequest(SwooleRequest $request): Request
    {
        $server = $request->server;

        return new Request(
            $server['request_method'] ?? 'GET',
            ($server['request_uri'] ?? '/') . (isset($server['query_string']) && !empty($server['query_string']) ? "?{$server['query_string']}" : ''),
            $request->header ?? [],
            $request->rawcontent(),
            $request->get ?? [],
            $request->cookie ?? [],
            $server ?? [],
            isset($request->header['x-request-id']) ? ['uid' => $request->header['x-request-id']] : [],
            !empty($request->files) ? \array_map(static fn (array $file) => new UploadedFile($file['tmp_name'], (int)$file['size'], (int)$file['error'], $file['name'], $file['type']), $request->files) : [],
            $request->post ?? [],
        );
    }
}
