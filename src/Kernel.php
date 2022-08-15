<?php

namespace App;

use App\Library\Helper;
use FastRoute\{
    DataGenerator\GroupCountBased,
    Dispatcher,
    Dispatcher\GroupCountBased as GroupCountBasedDispatcher,
    RouteCollector,
    RouteParser\Std
};
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

                $this->callController($request, $response, $class, $method);
                break;
        }
    }

    private function callController(Request $request, Response $response, $class, $method): void
    {
        // Create AbstractController
        if (!isset($this->container[$class])) {
            $this->container[$class] = new $class();
        }

        // Set Request|Response
        $this->container[$class]->set($request, $response);

        // Response
        try {
            $this->container[$class]->{$method}(...\array_values(\get_object_vars(\json_decode($request->getContent()))));
        } catch (\Throwable $ex) {
            $this->errorResponse($response, $ex->getMessage());
        }
    }

    private function errorResponse(Response $response, string $message = '404 not found!', int $code = 404): void
    {
        $response->header('Content-Type', 'application/json');
        $response->status($code);
        $response->end(json_encode(['code' => $code, 'message' => $message]));
    }
}
