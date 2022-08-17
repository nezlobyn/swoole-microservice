<?php

namespace App;

use App\Library\Helper;
use App\Library\PsrRequest;
use FastRoute\{
    DataGenerator\GroupCountBased,
    Dispatcher,
    Dispatcher\GroupCountBased as GroupCountBasedDispatcher,
    RouteCollector,
    RouteParser\Std
};
use Nyholm\Psr7\UploadedFile;
use Swoole\Http\{Request, Response};

class Kernel
{
    private array $container = [];

    /**
     * @var Dispatcher
     */
    private $router;

    public function __construct()
    {
        if (!$this->router) {
            // Load Routes
            $routes = yaml_parse_file(Helper::getRootDir('config/routes.yml'));

            // Create Routes
            $collector = new RouteCollector(new Std(), new GroupCountBased());
            foreach ($routes as $route) {
                $collector->addRoute($route['method'], $route['path'], str_replace('/', '\\', $route['controller']));
            }
            $this->router = new GroupCountBasedDispatcher($collector->getData());
        }
    }

    public function boot(Request $request, Response $response): void
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

                $this->callController($this->getPsrRequest($request), $response, $class, $method);
                break;
        }
    }

    private function callController(PsrRequest $request, Response $response, $class, $method): void
    {
        // Create AbstractController
        if (!isset($this->container[$class])) {
            $this->container[$class] = new $class();
        }

        // Set Request|Response
        $this->container[$class]->set($request, $response);

        // Response
        try {
            $this->container[$class]->{$method}(...\array_values($request->getParsedBody()));
        } catch (\Throwable $ex) {
            $this->errorResponse($response, $ex->getMessage());
        }
    }

    protected function getPsrRequest(Request $request): PsrRequest
    {
        $server = $request->server;

        return new PsrRequest(
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

    private function errorResponse(Response $response, string $message = '404 not found!', int $code = 404): void
    {
        $response->header('Content-Type', 'application/json');
        $response->status($code);
        $response->end(json_encode(['code' => $code, 'message' => $message]));
    }
}
