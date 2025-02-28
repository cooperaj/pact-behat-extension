<?php

declare(strict_types=1);

namespace Tests\SmartGamma\Behat\PactExtension\Infrastructure;

use PhpPact\Consumer\InteractionBuilder;
use PhpPact\Consumer\Model\ConsumerRequest;
use PhpPact\Consumer\Model\ProviderResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsUsingDeepClone;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SmartGamma\Behat\PactExtension\Extension;
use SmartGamma\Behat\PactExtension\Infrastructure\Factory\InteractionBuilderFactory;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\InteractionCompositor;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\InteractionDTO;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\InteractionRequestDTO;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\InteractionResponseDTO;
use SmartGamma\Behat\PactExtension\Infrastructure\Pact;

/**
 * @phpstan-import-type CommonConfiguration from Extension
 */
#[CoversClass(Pact::class)]
#[UsesClass(InteractionDTO::class)]
#[UsesClass(InteractionRequestDTO::class)]
#[UsesClass(InteractionResponseDTO::class)]
final class PactTest extends TestCase
{
    private const PROVIDER_NAME = 'some_provider_name';
    private const CONSUMER_NAME = 'some_consumer_name';

    private MockObject|InteractionBuilder $interactionBuilderMock;
    private MockObject|InteractionCompositor $interactionCompositorMock;
    private MockObject|ConsumerRequest $consumerRequestMock;
    private MockObject|ProviderResponse $providerResponseMock;

    private Pact $pact;

    protected function setUp(): void
    {
        $interactionBuilderFactoryMock   = $this->createMock(InteractionBuilderFactory::class);
        $this->interactionBuilderMock    = $this->createMock(InteractionBuilder::class);
        $this->interactionCompositorMock = $this->createMock(InteractionCompositor::class);
        $this->consumerRequestMock       = $this->createMock(ConsumerRequest::class);
        $this->providerResponseMock      = $this->createMock(ProviderResponse::class);

        $providerConfig[self::PROVIDER_NAME] = [
            'PACT_MOCK_SERVER_HOST' => 'localhost',
            'PACT_MOCK_SERVER_PORT' => '8090',
            'PACT_PROVIDER_NAME'    => self::PROVIDER_NAME,
        ];
        $config                              = [
            'PACT_CONSUMER_NAME'    => self::CONSUMER_NAME,
            'PACT_OUTPUT_DIR'       => '/',
            'PACT_CONSUMER_VERSION' => '1.0.1',
        ];

        $interactionBuilderFactoryMock
            ->method('create')
            ->willReturn($this->interactionBuilderMock);

        $this->pact = new Pact(
            $interactionBuilderFactoryMock,
            $this->interactionCompositorMock,
            $config,
            $providerConfig,
        );
    }

    /**
     * @return array{
     *     Pact,
     *     MockObject|InteractionBuilder
     * }
     */
    #[Test]
    public function registersInteraction(): array
    {
        $this->interactionBuilderMock
            ->expects($this->once())
            ->method('given')
            ->willReturn($this->interactionBuilderMock);
        $this->interactionBuilderMock
            ->expects($this->once())
            ->method('uponReceiving')
            ->willReturn($this->interactionBuilderMock);
        $this->interactionBuilderMock
            ->expects($this->once())
            ->method('with')->willReturn($this->interactionBuilderMock);
        $this->interactionBuilderMock
            ->expects($this->once())
            ->method('willRespondWith')
            ->willReturn(true);

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

        $requestDTO    = new InteractionRequestDTO(self::PROVIDER_NAME, 'upon text', '/');
        $responseDTO   = new InteractionResponseDTO(200, []);
        $providerState = 'dummy state';

        $this->pact->registerInteraction($requestDTO, $responseDTO, $providerState);

        return [$this->pact, $this->interactionBuilderMock];
    }

    /**
     * @param array{
     *     Pact,
     *     MockObject|InteractionBuilder
     * } $testDependencies
     */
    #[Test]
    #[Depends('registersInteraction')]
    public function verifiesInteractions(array $testDependencies): void
    {
        [$pact, $interactionBuilderMock] = $testDependencies;

        $interactionBuilderMock
            ->expects($this->once())
            ->method('verify')
            ->willReturn(true);

        $this->assertTrue($pact->verifyInteractions());
    }

    #[Test]
    public function returnsConsumerVersion(): void
    {
        $this->assertEquals('1.0.1', $this->pact->getConsumerVersion());
    }
}
