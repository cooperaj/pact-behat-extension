<?php

namespace spec\SmartGamma\Behat\PactExtension\Infrastructure;

use SmartGamma\Behat\PactExtension\Infrastructure\MockServerFactory;
use SmartGamma\Behat\PactExtension\Infrastructure\MockServerInterface;
use SmartGamma\Behat\PactExtension\Infrastructure\Pact;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PactSpec extends ObjectBehavior
{
    const PROVIDER_NAME = 'some_provider_name';
    const CONSUMER_NAME = 'some_consumer_name';
    const MOCK_SERVER_PID = 1000;

    function let(MockServerFactory $mockServerFactory, MockServerInterface $mockServer)
    {
        $providerConfig[self::PROVIDER_NAME]['PACT_MOCK_SERVER_HOST'] = 'localhost';
        $providerConfig[self::PROVIDER_NAME]['PACT_MOCK_SERVER_PORT'] = '8090';
        $providerConfig[self::PROVIDER_NAME]['PACT_PROVIDER_NAME'] = self::PROVIDER_NAME;
        $config['PACT_CONSUMER_NAME'] = self::CONSUMER_NAME;
        $config['PACT_OUTPUT_DIR'] = '/';
        $config['PACT_CORS'] = 'false';
        $config['PACT_MOCK_SERVER_HEALTH_CHECK_TIMEOUT'] = 10;

        $mockServer->start()->willReturn(self::MOCK_SERVER_PID);
        $mockServerFactory->create(Argument::any())->willReturn($mockServer);

        $this->beConstructedWith($mockServerFactory, $config, $providerConfig);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Pact::class);
    }

    public function it_start_provider_mock_server()
    {
        $this->startServer(self::PROVIDER_NAME)->shouldBe(self::MOCK_SERVER_PID);
    }

    public function it_start_singleton_provider_mock_server()
    {
        $this->startServer(self::PROVIDER_NAME)->shouldBe(self::MOCK_SERVER_PID);
        $this->startServer(self::PROVIDER_NAME)->shouldBe(self::MOCK_SERVER_PID);
    }

    public function it_verifies_interactions()
    {
        $this->verifyInteractions()->shouldBe(true);
    }
}
