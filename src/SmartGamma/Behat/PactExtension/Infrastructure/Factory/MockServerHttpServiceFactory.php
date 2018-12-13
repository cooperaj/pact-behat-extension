<?php

namespace SmartGamma\Behat\PactExtension\Infrastructure\Factory;

use PhpPact\Http\GuzzleClient;
use PhpPact\Standalone\MockService\MockServerConfigInterface;
use PhpPact\Standalone\MockService\Service\MockServerHttpService;

class MockServerHttpServiceFactory
{
    /**
     * @var GuzzleClient
     */
    private $client;

    public function __construct(GuzzleClient $client)
    {
        $this->client = $client;
    }

    public function create(MockServerConfigInterface $config): MockServerHttpService
    {
        return new MockServerHttpService($this->client, $config);
    }
}
