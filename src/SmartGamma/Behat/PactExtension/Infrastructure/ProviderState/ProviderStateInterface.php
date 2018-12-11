<?php

namespace SmartGamma\Behat\PactExtension\Infrastructure\ProviderState;

interface ProviderStateInterface
{
    public function getStateDescription(string $providerName): string;
}
