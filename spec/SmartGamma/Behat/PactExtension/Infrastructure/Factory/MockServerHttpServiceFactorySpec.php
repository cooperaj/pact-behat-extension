<?php

namespace spec\SmartGamma\Behat\PactExtension\Infrastructure\Factory;

use PhpPact\Http\GuzzleClient;
use PhpPact\Standalone\MockService\MockServerConfigInterface;
use PhpPact\Standalone\MockService\Service\MockServerHttpService;
use SmartGamma\Behat\PactExtension\Infrastructure\Factory\MockServerHttpServiceFactory;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MockServerHttpServiceFactorySpec extends ObjectBehavior
{
    function let(GuzzleClient $client)
    {
        $this->beConstructedWith($client);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MockServerHttpServiceFactory::class);
    }

    function it_creates_http_service(MockServerConfigInterface $config)
    {
        $this->create($config)->shouldBeAnInstanceOf(MockServerHttpService::class);
    }
}
