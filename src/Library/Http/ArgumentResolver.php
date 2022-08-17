<?php

namespace App\Library\Http;

use \ReflectionMethod;
use Swoole\Http\Response;

class ArgumentResolver
{
    use ResponseErrorTrait;

    public function getArguments(Request $request, Response $response, string $class, string $method): array
    {
        $necessaryArgumentsName = $this->getNecessaryArgumentsName($class, $method);

        $arguments = [];
        $receivedArguments = $request->getParsedBody();

        foreach ($necessaryArgumentsName as $argumentName => $isDefaultValueAvailableData) {
            switch (true) {
                case 'request' === $argumentName:
                    \array_push($arguments, $request);
                    break;
                case isset($receivedArguments[$argumentName]):
                    \array_push($arguments, $receivedArguments[$argumentName]);
                    break;
                case isset($receivedArguments[$camelToSnakeArgumentName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $argumentName))]):
                    \array_push($arguments, $receivedArguments[$camelToSnakeArgumentName]);
                    break;
                case $isDefaultValueAvailableData['is_default_value_available']:
                    \array_push($arguments, $isDefaultValueAvailableData['default_value']);
                    break;
                default:
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
            if ($param->isDefaultValueAvailable()) {
                $requiredParams[$param->name] = ['is_default_value_available' => $param->isDefaultValueAvailable(), 'default_value' =>  $param->getDefaultValue()];
            } else {
                $requiredParams[$param->name] = ['is_default_value_available' => $param->isDefaultValueAvailable()];
            }
        }

        return $requiredParams;
    }
}
