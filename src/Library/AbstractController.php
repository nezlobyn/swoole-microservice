<?php

namespace App\Library;

use App\Library\Http\Request;
use Swoole\Http\Response;

class AbstractController
{
    protected Request $request;

    protected Response $response;

    final function set(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function jsonResponse(array $data = []): void
    {
        $this->response->header('Content-Type', 'application/json');
        $this->response->end(json_encode($data));
    }

    public function errorResponse(string $message = '404 not found!', int $code = 404): void
    {
        $this->response->header('Content-Type', 'application/json');
        $this->response->status($code);
        $this->response->end(json_encode(['code' => $code, 'message' => $message]));
    }
}
