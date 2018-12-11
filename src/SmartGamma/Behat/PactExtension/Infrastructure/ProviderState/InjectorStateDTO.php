<?php

namespace SmartGamma\Behat\PactExtension\Infrastructure\ProviderState;

class InjectorStateDTO
{
    /**
     * @var string
     */
    private $providerName;

    /**
     * @var string
     */
    private $entityName;

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var string
     */
    private $entityDescription;

    public function __construct(string $providerName, string $entityName, array $parameters, ?string $entityDescription = null)
    {
        $this->providerName      = $providerName;
        $this->entityName        = $entityName;
        $this->entityDescription = $entityDescription ? '(' . $entityDescription . ')' : '';
        $this->parameters        = $parameters;
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
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @return string|null
     */
    public function getEntityDescription()
    {
        return $this->entityDescription;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
