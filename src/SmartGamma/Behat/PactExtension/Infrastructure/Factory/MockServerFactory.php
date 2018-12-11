<?php

namespace SmartGamma\Behat\PactExtension\Infrastructure\Factory;

use PhpPact\Standalone\MockService\MockServer;
use PhpPact\Standalone\MockService\MockServerConfigInterface;

class MockServerFactory
{
    public function create(MockServerConfigInterface $config)
    {
        return new MockServer($config);
    }
}
