<?php

declare(strict_types=1);

namespace spec\SmartGamma\Behat\PactExtension\Infrastructure;

use PhpPact\Broker\Service\BrokerHttpClient;
use PhpPact\Consumer\InteractionBuilder;
use PhpPact\Consumer\Model\ConsumerRequest;
use PhpPact\Consumer\Model\ProviderResponse;
use PhpPact\Standalone\MockService\Service\MockServerHttpService;
use SmartGamma\Behat\PactExtension\Infrastructure\Factory\BrokerHttpClientFactory;
use SmartGamma\Behat\PactExtension\Infrastructure\Factory\InteractionBuilderFactory;
use SmartGamma\Behat\PactExtension\Infrastructure\Factory\MockServerHttpServiceFactory;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\InteractionCompositor;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\InteractionRequestDTO;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\InteractionResponseDTO;
use SmartGamma\Behat\PactExtension\Infrastructure\Factory\MockServerFactory;
use SmartGamma\Behat\PactExtension\Infrastructure\Factory\MockServerInterface;
use SmartGamma\Behat\PactExtension\Infrastructure\Pact;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PactSpec extends ObjectBehavior
{
    const PROVIDER_NAME = 'some_provider_name';
    const CONSUMER_NAME = 'some_consumer_name';
    const MOCK_SERVER_PID = 1000;
    const CONSUMER_VERSION = '1.0.0';

    function let(
        MockServerFactory $mockServerFactory,
        MockServerInterface $mockServer,
        InteractionBuilderFactory $interactionBuilderFactory,
        MockServerHttpServiceFactory $mockServerHttpServiceFactory,
        MockServerHttpService $mockServerHttpService,
        InteractionBuilder $interactionBuilder,
        InteractionCompositor $interactionCompositor,
        BrokerHttpClientFactory $brokerHttpClientFactory,
        BrokerHttpClient $brokerHttpClient,
        ConsumerRequest $consumerRequest,
        ProviderResponse $providerResponse
    )
    {
        $providerConfig[self::PROVIDER_NAME]['PACT_MOCK_SERVER_HOST'] = 'localhost';
        $providerConfig[self::PROVIDER_NAME]['PACT_MOCK_SERVER_PORT'] = 8090;
        $providerConfig[self::PROVIDER_NAME]['PACT_PROVIDER_NAME'] = self::PROVIDER_NAME;
        $config['PACT_CONSUMER_NAME'] = self::CONSUMER_NAME;
        $config['PACT_OUTPUT_DIR'] = '/';
        $config['PACT_CORS'] = 'false';
        $config['PACT_BROKER_URI'] = 'http://pact.domain.com';
        $config['PACT_CONSUMER_VERSION'] = '1.0.1';
        $config['PACT_MOCK_SERVER_HEALTH_CHECK_TIMEOUT'] = 10;

        $pactJson = '{"consumer": { "name": "some_consumer" }, "provider": { "name": "some_provider"} }';

        $mockServer->start()->willReturn(self::MOCK_SERVER_PID);
        $mockServerFactory->create(Argument::any())->willReturn($mockServer);

        $interactionBuilder->given(Argument::any())->willReturn($interactionBuilder);
        $interactionBuilder->uponReceiving(Argument::any())->willReturn($interactionBuilder);
        $interactionBuilder->with(Argument::any())->willReturn($interactionBuilder);
        $interactionBuilder->willRespondWith(Argument::any())->willReturn(true);
        $interactionBuilder->verify()->willReturn(true);

        $interactionBuilderFactory->create(Argument::any())->willReturn($interactionBuilder);

        $mockServerHttpService->verifyInteractions()->willReturn(true);
        $mockServerHttpService->deleteAllInteractions()->willReturn(true);
        $mockServerHttpService->getPactJson()->willReturn($pactJson);
        $mockServerHttpServiceFactory->create(Argument::any())->willReturn($mockServerHttpService);

        $interactionCompositor->createRequestFromDTO(Argument::type(InteractionRequestDTO::class))
            ->willReturn($consumerRequest);
        $interactionCompositor->createResponseFromDTO(Argument::type(InteractionResponseDTO::class))
            ->willReturn($providerResponse);

        $brokerHttpClient->publishJson($pactJson, $config['PACT_CONSUMER_VERSION']);
        $brokerHttpClient
            ->tag(Argument::type('string'), Argument::type('string'), Argument::type('string'));
        $brokerHttpClientFactory->create()->willReturn($brokerHttpClient);

        $this->beConstructedWith(
            $mockServerFactory,
            $interactionBuilderFactory,
            $mockServerHttpServiceFactory,
            $interactionCompositor,
            $brokerHttpClientFactory,
            $config,
            $providerConfig
        );
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

    public function it_finalizes_testing()
    {
        $this->startServer(self::PROVIDER_NAME);
        $this->finalize(self::CONSUMER_VERSION)->shouldBe(true);
    }

    public function it_finalizes_testing_and_skip_broker_upload_if_no_api_servers_was_started(MockServerHttpService $service)
    {
        $service->getPactJson()->shouldNotBeCalled();
        $this->finalize(self::CONSUMER_VERSION)->shouldBe(true);
    }

    public function it_register_interaction()
    {
        $requestDTO = new InteractionRequestDTO(self::PROVIDER_NAME,'upon text','/');
        $responseDTO = new InteractionResponseDTO(200, []);
        $providerState = 'dummy state';

        $this->registerInteraction($requestDTO, $responseDTO, $providerState);
    }

    public function it_returns_consumer_version()
    {
        $this->getConsumerVersion()->shouldBeString();
    }
}
