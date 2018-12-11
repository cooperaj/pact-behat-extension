<?php

namespace SmartGamma\Behat\PactExtension\Infrastructure;

class InteractionRequestDTO
{
    /**
     * @var string
     */
    private $providerName;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var string | null
     */
    private $query;

    /**
     * @var array
     */
    private $body;

    /**
     * InteractionRequestDTO constructor.
     *
     * @param string      $providerName
     * @param string      $description
     * @param string      $uri
     * @param string      $method
     * @param string|null $query
     * @param array|null  $body
     */
    public function __construct(
        string $providerName,
        string $description,
        string $uri,
        string $method = 'GET',
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
    }

    /**
     * @return string
     */
    public function getProviderName(): string
    {
        return $this->providerName;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string|null
     */
    public function getQuery(): ?string
    {
        return $this->query;
    }

    /**
     * @return array
     */
    public function getBody(): array
    {
        return $this->body;
    }
}
