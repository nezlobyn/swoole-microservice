<?php

namespace App\Library\Http;

use Nyholm\Psr7\{MessageTrait, RequestTrait, Stream, Uri};
use Psr\Http\Message\{ServerRequestInterface, UploadedFileInterface};

class Request implements ServerRequestInterface
{
    use MessageTrait;
    use RequestTrait;

    protected array $attributes = [];

    protected array $cookieParams = [];

    protected $parsedBody;

    protected array $queryParams = [];

    protected array $serverParams;

    /** @var UploadedFileInterface[] */
    protected array $uploadedFiles = [];

    public function __construct(string $method, string $uri, array $headers = [], string $body = null, array $queryParams = [], array $cookieParams = [], array $serverParams = [], array $attributes = [], array $files = [], array $postParams = [])
    {
        $this->method = $method;
        $this->uri = new Uri($uri);
        $this->setHeaders($headers);
        $this->protocol = '1.0';
        $this->stream = Stream::create($body ?? '');
        $this->stream->rewind();
        $this->queryParams = $queryParams;
        $this->cookieParams = $cookieParams;
        $this->serverParams = $serverParams;
        $this->attributes = $attributes;
        $this->uploadedFiles = $files;

        if ('GET' === $method) {
            $this->parsedBody = !empty($body) ? $this->queryParams : [];
        } else {
            $this->parsedBody = !empty($body) ? \get_object_vars(\json_decode($body)) : [];
        }
    }

    public function withHeader($header, $value)
    {
        $normalized = \strtolower($header);
        $new = clone $this;

        if (isset($new->headerNames[$normalized])) {
            unset($new->headers[$new->headerNames[$normalized]]);
        }
        $new->headerNames[$normalized] = $header;
        $new->headers[$header] = (array)$value;

        return $new;
    }

    public function jsonSerialize()
    {
        return [
            'path' => (string)$this->uri,
            'method' => $this->method,
            'query' => $this->queryParams,
            'headers' => $this->headers,
            'attributes' => $this->attributes,
        ];
    }

    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    public function withUploadedFiles(array $uploadedFiles)
    {
        $new = clone $this;
        $new->uploadedFiles = $uploadedFiles;

        return $new;
    }

    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    public function withCookieParams(array $cookies)
    {
        $new = clone $this;
        $new->cookieParams = $cookies;

        return $new;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function withQueryParams(array $query)
    {
        $new = clone $this;
        $new->queryParams = $query;

        return $new;
    }

    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @param mixed $data
     */
    public function withParsedBody($data)
    {
        if (!\is_array($data) && null !== $data) {
            throw new \InvalidArgumentException('First parameter to withParsedBody MUST be object, array or null');
        }

        $new = clone $this;
        $new->parsedBody = $data;

        return $new;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute($attribute, $default = null)
    {
        if (false === \array_key_exists($attribute, $this->attributes)) {
            return $default;
        }

        return $this->attributes[$attribute];
    }

    public function withAttribute($attribute, $value): self
    {
        $new = clone $this;
        $new->attributes[$attribute] = $value;

        return $new;
    }

    public function withoutAttribute($attribute): self
    {
        if (false === \array_key_exists($attribute, $this->attributes)) {
            return $this;
        }

        $new = clone $this;
        unset($new->attributes[$attribute]);

        return $new;
    }

    public function getHeaderNames(): array
    {
        return $this->headerNames;
    }

    protected function setHeaders(array $headers)
    {
        $this->headerNames = $this->headers = [];

        foreach ($headers as $header => $value) {
            $this->headerNames[\strtolower($header)] = $header;
            $this->headers[$header] = (array)$value;
        }
    }

    protected function parseQuery($str): array
    {
        $result = [];

        if ('' === $str) {
            return $result;
        }

        foreach (\explode('&', $str) as $kvp) {
            $parts = \explode('=', $kvp, 2);
            $key = \rawurldecode(\str_replace('+', ' ', $parts[0]));
            $value = isset($parts[1]) ? \rawurldecode(\str_replace('+', ' ', $parts[1])) : null;

            if (!isset($result[$key])) {
                $result[$key] = $value;
            } else {
                if (!\is_array($result[$key])) {
                    $result[$key] = [$result[$key]];
                }
                $result[$key][] = $value;
            }
        }

        return $result;
    }

}

