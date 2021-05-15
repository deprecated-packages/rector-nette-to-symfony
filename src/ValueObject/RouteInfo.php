<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\ValueObject;

final class RouteInfo
{
    /**
     * @param string[] $httpMethods
     */
    public function __construct(
        private string $class,
        private string $method,
        private string $path,
        private array $httpMethods = [
        ]
    ) {
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string[]
     */
    public function getHttpMethods(): array
    {
        return $this->httpMethods;
    }
}
