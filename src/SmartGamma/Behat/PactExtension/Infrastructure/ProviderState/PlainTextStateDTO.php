<?php

declare(strict_types=1);

namespace SmartGamma\Behat\PactExtension\Infrastructure\ProviderState;

class PlainTextStateDTO
{
    private string $providerName;

    private string $description;

    public function __construct(string $providerName, string $description)
    {
        $this->providerName = $providerName;
        $this->description  = $description;
    }

    public function getProviderName(): string
    {
        return $this->providerName;
    }

    public function getStateDescription(): string
    {
        return $this->description;
    }
}
