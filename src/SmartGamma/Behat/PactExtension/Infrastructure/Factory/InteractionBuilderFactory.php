<?php

namespace SmartGamma\Behat\PactExtension\Infrastructure\Factory;

use PhpPact\Consumer\InteractionBuilder;
use PhpPact\Standalone\MockService\MockServerConfigInterface;

class InteractionBuilderFactory
{
    public function create(MockServerConfigInterface $config)
    {
        return new InteractionBuilder($config);
    }
}
