<?php

declare(strict_types=1);

namespace SmartGamma\Behat\PactExtension\Infrastructure\ProviderState;

class PlainTextStateDTO
{
    public function __construct(private string $providerName, private string $description)
    {
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
