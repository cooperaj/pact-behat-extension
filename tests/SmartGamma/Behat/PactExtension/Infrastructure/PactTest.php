<?php

declare(strict_types=1);

namespace Tests\SmartGamma\Behat\PactExtension\Infrastructure;

use PhpPact\Standalone\MockService\MockServer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
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

#[CoversClass(Pact::class)]
#[UsesClass(InteractionRequestDTO::class)]
#[UsesClass(InteractionResponseDTO::class)]
final class PactTest extends TestCase
{
    const PROVIDER_NAME = 'some_provider_name';
    const CONSUMER_NAME = 'some_consumer_name';
    const MOCK_SERVER_PID = 1000;
    const CONSUMER_VERSION = '1.0.0';

    private MockObject|MockServerFactory $mockServerFactoryMock;
    private MockObject|MockServerInterface $mockServerMock;
    private MockObject|InteractionBuilderFactory $interactionBuilderFactoryMock;
    private MockObject|MockServerHttpServiceFactory $mockServerHttpServiceFactoryMock;
    private MockObject|MockServerHttpService $mockServerHttpServiceMock;
    private MockObject|InteractionBuilder $interactionBuilderMock;
    private MockObject|InteractionCompositor $interactionCompositorMock;
    private MockObject|BrokerHttpClientFactory $brokerHttpClientFactoryMock;
    private MockObject|BrokerHttpClient $brokerHttpClientMock;
    private MockObject|ConsumerRequest $consumerRequestMock;
    private MockObject|ProviderResponse $providerResponseMock;

    private array $config;

    private Pact $pact;

    protected function setUp(): void
    {
        $this->mockServerFactoryMock = $this->createMock(MockServerFactory::class);
        $this->mockServerMock = $this->createMock(MockServer::class);
        $this->interactionBuilderFactoryMock = $this->createMock(InteractionBuilderFactory::class);
        $this->mockServerHttpServiceFactoryMock = $this->createMock(MockServerHttpServiceFactory::class);
        $this->mockServerHttpServiceMock = $this->createMock(MockServerHttpService::class);
        $this->interactionBuilderMock = $this->createMock(InteractionBuilder::class);
        $this->interactionCompositorMock = $this->createMock(InteractionCompositor::class);
        $this->brokerHttpClientFactoryMock = $this->createMock(BrokerHttpClientFactory::class);
        $this->brokerHttpClientMock = $this->createMock(BrokerHttpClient::class);
        $this->consumerRequestMock = $this->createMock(ConsumerRequest::class);
        $this->providerResponseMock = $this->createMock(ProviderResponse::class);

        $providerConfig[self::PROVIDER_NAME] = [
            'PACT_MOCK_SERVER_HOST' => 'localhost',
            'PACT_MOCK_SERVER_PORT' => 8090,
            'PACT_PROVIDER_NAME' => self::PROVIDER_NAME,
        ];
        $this->config = [
            'PACT_CONSUMER_NAME' => self::CONSUMER_NAME,
            'PACT_OUTPUT_DIR' => '/',
            'PACT_CORS' => 'false',
            'PACT_BROKER_URI' => 'http://pact.domain.com',
            'PACT_CONSUMER_VERSION' => '1.0.1',
            'PACT_MOCK_SERVER_HEALTH_CHECK_TIMEOUT' => 10,
        ];

//        $this->mockServerMock->expects($this->once())->method('start')->willReturn(self::MOCK_SERVER_PID);
//        $this->mockServerFactoryMock->expects($this->once())->method('create')->willReturn($this->mockServerMock);

//        $this->interactionBuilderMock->expects($this->once())->method('given')->willReturn($this->interactionBuilderMock);
//        $this->interactionBuilderMock->expects($this->once())->method('uponReceiving')->willReturn($this->interactionBuilderMock);
//        $this->interactionBuilderMock->with(Argument::any())->willReturn($this->interactionBuilderMock);
//        $this->interactionBuilderMock->expects($this->once())->method('willRespondWith')->willReturn(true);
//        $this->interactionBuilderMock->expects($this->once())->method('verify')->willReturn(true);
//        $this->interactionBuilderFactoryMock->expects($this->once())->method('create')->willReturn($this->interactionBuilderMock);

//        $this->mockServerHttpServiceMock->expects($this->once())->method('verifyInteractions')->willReturn(true);
//        $this->mockServerHttpServiceMock->expects($this->once())->method('deleteAllInteractions')->willReturn(true);
//        $this->mockServerHttpServiceMock->expects($this->once())->method('getPactJson')->willReturn($pactJson);
//        $this->mockServerHttpServiceFactoryMock->expects($this->once())->method('create')->willReturn($this->mockServerHttpServiceMock);

//        $this->interactionCompositorMock->expects($this->once())->method('createRequestFromDTO')->with($this->isInstanceOf(InteractionRequestDTO::class))
//            ->willReturn($this->consumerRequestMock);
//        $this->interactionCompositorMock->expects($this->once())->method('createResponseFromDTO')->with($this->isInstanceOf(InteractionResponseDTO::class))
//            ->willReturn($this->providerResponseMock);

//        $this->brokerHttpClientMock->publishJson($pactJson, $config['PACT_CONSUMER_VERSION']);
//        $this->brokerHttpClientMock
//            ->tag(Argument::type('string'), Argument::type('string'), Argument::type('string'));
//        $this->brokerHttpClientFactoryMock->expects($this->once())->method('create')->willReturn($this->brokerHttpClientMock);

        $this->mockServerFactoryMock
            ->method('create')
            ->willReturn($this->mockServerMock);

        $this->interactionBuilderFactoryMock
            ->method('create')
            ->willReturn($this->interactionBuilderMock);

        $this->mockServerHttpServiceFactoryMock
            ->method('create')
            ->willReturn($this->mockServerHttpServiceMock);

        $this->brokerHttpClientFactoryMock
            ->method('create')
            ->willReturn($this->brokerHttpClientMock);

        $this->pact = new Pact(
            $this->mockServerFactoryMock,
            $this->interactionBuilderFactoryMock,
            $this->mockServerHttpServiceFactoryMock,
            $this->interactionCompositorMock,
            $this->brokerHttpClientFactoryMock,
            $this->config,
            $providerConfig
        );
    }

    #[Test]
    public function startProviderMockServer(): void
    {
        $this->mockServerMock->expects($this->once())->method('start')->willReturn(self::MOCK_SERVER_PID);

        $this->assertSame(self::MOCK_SERVER_PID, $this->pact->startServer(self::PROVIDER_NAME));
    }

    #[Test]
    public function startSingletonProviderMockServer(): void
    {
        $this->mockServerMock->expects($this->once())->method('start')->willReturn(self::MOCK_SERVER_PID);

        $this->assertSame(self::MOCK_SERVER_PID, $this->pact->startServer(self::PROVIDER_NAME));
        $this->assertSame(self::MOCK_SERVER_PID, $this->pact->startServer(self::PROVIDER_NAME));
    }

    #[Test]
    public function verifiesInteractions(): void
    {
        $this->interactionBuilderMock->expects($this->once())->method('verify')->willReturn(true);

        $this->assertTrue($this->pact->verifyInteractions());
    }

    #[Test]
    public function finalizesTesting(): void
    {
        $pactJson = '{"consumer": { "name": "some_consumer" }, "provider": { "name": "some_provider"} }';

        $this->mockServerHttpServiceMock->expects($this->once())->method('verifyInteractions')->willReturn(true);
        $this->mockServerHttpServiceMock->expects($this->once())->method('getPactJson')->willReturn($pactJson);

        $this->brokerHttpClientMock->publishJson($pactJson, $this->config['PACT_CONSUMER_VERSION']);

        $this->pact->startServer(self::PROVIDER_NAME);
        $this->assertTrue($this->pact->finalize(self::CONSUMER_VERSION));
    }

    #[Test]
    public function finalizesTestingAndSkipsBrokerUploadIfNoApiServersWasStarted(): void
    {
        $this->mockServerHttpServiceMock->expects($this->never())->method('verifyInteractions');
        $this->mockServerHttpServiceMock->expects($this->never())->method('getPactJson');

        $this->assertTrue($this->pact->finalize(self::CONSUMER_VERSION));
    }

    #[Test]
    public function registersInteraction(): void
    {
        $this->interactionBuilderMock->expects($this->once())->method('given')->willReturn($this->interactionBuilderMock);
        $this->interactionBuilderMock->expects($this->once())->method('uponReceiving')->willReturn($this->interactionBuilderMock);
        $this->interactionBuilderMock->expects($this->once())->method('with')->willReturn($this->interactionBuilderMock);
        $this->interactionBuilderMock->expects($this->once())->method('willRespondWith')->willReturn(true);

        $this->interactionCompositorMock
            ->expects($this->once())
            ->method('createRequestFromDTO')
            ->with($this->isInstanceOf(InteractionRequestDTO::class))
            ->willReturn($this->consumerRequestMock);
        $this->interactionCompositorMock
            ->expects($this->once())
            ->method('createResponseFromDTO')
            ->with($this->isInstanceOf(InteractionResponseDTO::class))
            ->willReturn($this->providerResponseMock);

        $requestDTO = new InteractionRequestDTO(self::PROVIDER_NAME,'upon text','/');
        $responseDTO = new InteractionResponseDTO(200, []);
        $providerState = 'dummy state';

        $this->pact->registerInteraction($requestDTO, $responseDTO, $providerState);
    }

    #[Test]
    public function cleansUpInteractions(): void
    {
        $this->mockServerHttpServiceMock->expects($this->once())->method('deleteAllInteractions');

        $this->assertTrue($this->pact->cleanupInteractions());
    }

    #[Test]
    public function returnsConsumerVersion(): void
    {
        $this->assertEquals('1.0.1', $this->pact->getConsumerVersion());
    }
}
