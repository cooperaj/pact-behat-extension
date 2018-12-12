<?php

namespace spec\SmartGamma\Behat\PactExtension\Infrastructure;

use PhpPact\Consumer\InteractionBuilder;
use PhpPact\Consumer\Model\ConsumerRequest;
use PhpPact\Consumer\Model\ProviderResponse;
use SmartGamma\Behat\PactExtension\Infrastructure\Factory\InteractionBuilderFactory;
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
        InteractionBuilder $interactionBuilder,
        InteractionCompositor $interactionCompositor,
        ConsumerRequest $consumerRequest,
        ProviderResponse $providerResponse
    )
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

        $interactionBuilder->given(Argument::any())->willReturn($interactionBuilder);
        $interactionBuilder->uponReceiving(Argument::any())->willReturn($interactionBuilder);
        $interactionBuilder->with(Argument::any())->willReturn($interactionBuilder);
        $interactionBuilder->willRespondWith(Argument::any())->willReturn(true);

        $interactionBuilderFactory->create(Argument::any())->willReturn($interactionBuilder);

        $interactionCompositor->createRequestFromDTO(Argument::type(InteractionRequestDTO::class))->willReturn($consumerRequest);
        $interactionCompositor->createResponseFromDTO(Argument::type(InteractionResponseDTO::class))->willReturn($providerResponse);

        $this->beConstructedWith($mockServerFactory, $interactionBuilderFactory, $interactionCompositor, $config, $providerConfig);
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
        $this->finalize(self::CONSUMER_VERSION)->shouldBe(true);
    }

    public function it_register_interaction()
    {
        $requestDTO = new InteractionRequestDTO(self::PROVIDER_NAME,'upon text','/');
        $responseDTO = new InteractionResponseDTO(200, []);
        $providerState = 'dummy state';

        $this->registerInteraction($requestDTO, $responseDTO, $providerState);
    }
}
