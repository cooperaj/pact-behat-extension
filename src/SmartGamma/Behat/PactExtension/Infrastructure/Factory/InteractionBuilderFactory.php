<?php

declare(strict_types=1);

namespace SmartGamma\Behat\PactExtension\Infrastructure\Factory;

use PhpPact\Consumer\InteractionBuilder;
use PhpPact\Standalone\MockService\MockServerConfigInterface;

class InteractionBuilderFactory
{
    public function create(MockServerConfigInterface $config): InteractionBuilder
    {
        return new InteractionBuilder($config);
    }
}
