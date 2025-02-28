<?php

declare(strict_types=1);

namespace SmartGamma\Behat\PactExtension\Infrastructure\Interaction;

class InteractionRequestDTO
{
    /**
     * @param string                         $providerName
     * @param string                         $description
     * @param string                         $uri
     * @param string                         $method
     * @param array<string, string>          $headers
     * @param array<string, string|string[]> $query
     * @param array<string, string|scalar[]> $body
     */
    public function __construct(
        private readonly string $providerName,
        private readonly string $description,
        private readonly string $uri,
        private readonly string $method = 'GET',
        private readonly array $headers = [],
        private readonly array $query = [],
        private readonly array $body = [],
    ) {
    }

    public function getProviderName(): string
    {
        return $this->providerName;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return array<string, string|string[]>
     */
    public function getQuery(): array
    {
        return $this->query;
    }

    /**
     * @return array<string, string|scalar[]>
     */
    public function getBody(): array
    {
        return $this->body;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
