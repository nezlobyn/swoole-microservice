<?php

namespace App\Library\Http;

use \ReflectionMethod;
use Swoole\Http\Response;

class ArgumentResolver
{
    use ResponseErrorTrait;

    public function getArguments(Request $request, Response $response, string $class, string $method): array
    {
        // Necessary arguments for the method
        $necessaryArgumentsName = $this->getNecessaryArgumentsName($class, $method);

        $arguments = [];
        $receivedArguments = $request->getParsedBody();

        foreach (\array_values($necessaryArgumentsName) as $argumentName) {
            if ('request' === $argumentName) {
                \array_push($arguments, $request);
                continue;
            }

            if (isset($receivedArguments[$argumentName])) {
                \array_push($arguments, $receivedArguments[$argumentName]);
                continue;
            }
            $camelToSnakeArgumentName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $argumentName));;

            if (isset($receivedArguments[$camelToSnakeArgumentName])) {
                \array_push($arguments, $receivedArguments[$camelToSnakeArgumentName]);
            } else {
                $this->errorResponse($response, "missed `$argumentName` value");
            }
        }

        return \array_values($arguments);
    }

    protected function getNecessaryArgumentsName(string $class, string $method): array
    {
        $ref = new ReflectionMethod($class, $method);
        $requiredParams = [];

        foreach ($ref->getParameters() as $param) {
            \array_push($requiredParams, $param->name);
        }

        return $requiredParams;
    }
}
