<?php
declare(strict_types=1);

namespace App\Library;

use Swoole\Http\Response;

trait ResponseErrorTrait
{
    private function errorResponse(Response $response, string $message = '404 not found!', int $code = 404): void
    {
        $response->header('Content-Type', 'application/json');
        $response->status($code);
        $response->end(json_encode(['code' => $code, 'message' => $message]));
    }
}
