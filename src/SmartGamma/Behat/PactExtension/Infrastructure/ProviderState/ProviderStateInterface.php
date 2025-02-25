<?php

declare(strict_types=1);

namespace SmartGamma\Behat\PactExtension\Infrastructure\ProviderState;

interface ProviderStateInterface
{
    public function getStateDescription(string $providerName): string;
}
