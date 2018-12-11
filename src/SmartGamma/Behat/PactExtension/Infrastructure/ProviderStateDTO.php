<?php

namespace SmartGamma\Behat\PactExtension\Infrastructure;

class ProviderStateDTO
{
    /**
     * @var string
     */
    private $providerName;

    /**
     * @var string
     */
    private $entity;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var string | null
     */
    private $description;

    /**
     * ProviderStateDTO constructor.
     *
     * @param string      $providerName
     * @param string      $entity
     * @param array       $parameters
     * @param string|null $description
     */
    public function __construct(string $providerName, string $entity, array $parameters, ?string $description = null)
    {
        $this->providerName = $providerName;
        $this->description  = $description;
        $this->entity       = $entity;
        $this->parameters   = $parameters;
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
    public function getEntity(): string
    {
        return $this->entity;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}
