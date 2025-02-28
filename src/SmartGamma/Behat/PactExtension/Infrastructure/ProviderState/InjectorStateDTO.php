<?php

declare(strict_types=1);

namespace SmartGamma\Behat\PactExtension\Infrastructure\ProviderState;

class InjectorStateDTO
{
    private string $entityDescription;

    /**
     * @param string      $providerName
     * @param string      $entityName
     * @param mixed[]     $parameters
     * @param string|null $entityDescription
     */
    public function __construct(
        private string $providerName,
        private string $entityName,
        private array $parameters = [],
        ?string $entityDescription = null,
    ) {
        $this->entityDescription = $entityDescription ? '(' . $entityDescription . ')' : '';
    }

    public function getProviderName(): string
    {
        return $this->providerName;
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function getEntityDescription(): string
    {
        return $this->entityDescription;
    }

    /**
     * @return mixed[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
