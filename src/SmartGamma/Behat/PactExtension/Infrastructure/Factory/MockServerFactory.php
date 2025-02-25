<?php

declare(strict_types=1);

namespace SmartGamma\Behat\PactExtension\Infrastructure\Factory;

use PhpPact\Standalone\MockService\MockServer;
use PhpPact\Standalone\MockService\MockServerConfig;
use PhpPact\Standalone\MockService\MockServerConfigInterface;

class MockServerFactory
{
    /**
     * @param MockServerConfig $config
     *
     * @return MockServer
     */
    public function create(MockServerConfigInterface $config): MockServer
    {
        return new MockServer($config);
    }
}
