<?php

declare(strict_types=1);

namespace SmartGamma\Behat\PactExtension\Infrastructure\Interaction;

class InteractionRequestDTO
{
    private string $providerName;

    private string $description;

    private string $method;

    private string $uri;

    private ?string $query;

    /** @var mixed[] */
    private array $body;

    /** @var string[] */
    private array $headers = [];

    /**
     * @param string      $providerName
     * @param string      $description
     * @param string      $uri
     * @param string      $method
     * @param string[]    $headers
     * @param string|null $query
     * @param array       $body
     */
    public function __construct(
        string $providerName,
        string $description,
        string $uri,
        string $method = 'GET',
        array $headers = [],
        ?string $query = null,
        array $body = []
    )
    {
        $this->providerName = $providerName;
        $this->description  = $description;
        $this->uri          = $uri;
        $this->method       = $method;
        $this->query        = $query;
        $this->body         = $body;
        $this->headers      = $headers;
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

    public function getQuery(): ?string
    {
        return $this->query;
    }

    /**
     * @return mixed[]
     */
    public function getBody(): array
    {
        return $this->body;
    }

    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
