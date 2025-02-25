<?php

declare(strict_types=1);

namespace SmartGamma\Behat\PactExtension\Infrastructure\Factory;

use PhpPact\Http\GuzzleClient;
use PhpPact\Standalone\MockService\MockServerConfigInterface;
use PhpPact\Standalone\MockService\Service\MockServerHttpService;

class MockServerHttpServiceFactory
{
    private GuzzleClient $client;

    public function __construct(GuzzleClient $client)
    {
        $this->client = $client;
    }

    public function create(MockServerConfigInterface $config): MockServerHttpService
    {
        return new MockServerHttpService($this->client, $config);
    }
}
