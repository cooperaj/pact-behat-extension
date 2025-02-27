<?php

declare(strict_types=1);

namespace SmartGamma\Behat\PactExtension\Infrastructure;

use PhpPact\Config\PactConfigInterface;
use PhpPact\Consumer\InteractionBuilder;
use PhpPact\Standalone\MockService\MockServerConfig;
use PhpPact\Standalone\MockService\MockServerConfigInterface;
use SmartGamma\Behat\PactExtension\Extension;
use SmartGamma\Behat\PactExtension\Infrastructure\Factory\InteractionBuilderFactory;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\InteractionCompositor;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\InteractionRequestDTO;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\InteractionResponseDTO;

/**
 * @phpstan-import-type CommonConfiguration from Extension
 * @phpstan-import-type ProviderConfiguration from Extension
 */
class Pact
{
    private InteractionBuilderFactory $interactionBuilderFactory;
    private InteractionCompositor $interactionCompositor;

    /** @psalm-var CommonConfiguration */
    private array $config;

    /** @psalm-var ProviderConfiguration */
    private array $providersConfig;

    /** @var MockServerConfigInterface[] $mockServerConfigs */
    private array $mockServerConfigs = [];

    /** @var InteractionBuilder[] $builders */
    private array $builders = [];

    /**
     * @param InteractionBuilderFactory     $interactionBuilderFactory
     * @param InteractionCompositor         $interactionCompositor
     * @param array                         $config
     * @phpstan-param CommonConfiguration   $config
     * @param array                         $providersConfig
     * @phpstan-param ProviderConfiguration $providersConfig
     */
    public function __construct(
        InteractionBuilderFactory $interactionBuilderFactory,
        InteractionCompositor $interactionCompositor,
        array $config,
        array $providersConfig
    ) {
        $this->interactionBuilderFactory    = $interactionBuilderFactory;
        $this->interactionCompositor        = $interactionCompositor;
        $this->config                       = $config;
        $this->providersConfig              = $providersConfig;
        $this->registerMockServerConfigs();
        $this->registerBuilders();
    }


    private function registerMockServerConfigs(): void
    {
        foreach ($this->providersConfig as $providerName => $providerConfig) {
            $this->mockServerConfigs[$providerName] = $this->createMockServerConfig($providerConfig);
        }
    }

    private function registerBuilders(): void
    {
        foreach ($this->mockServerConfigs as $providerName => $mockServerConfig) {
            $this->builders[$providerName] = $this->interactionBuilderFactory->create($mockServerConfig);
        }
    }

    /**
     * @param string[] $providerConfig
     *
     * @return MockServerConfigInterface
     */
    private function createMockServerConfig(array $providerConfig): MockServerConfigInterface
    {
        $config = new MockServerConfig();
        $config
            ->setHost($providerConfig['PACT_MOCK_SERVER_HOST'])
            ->setPort((int) $providerConfig['PACT_MOCK_SERVER_PORT'])
            ->setProvider($providerConfig['PACT_PROVIDER_NAME'])
            ->setConsumer($this->config['PACT_CONSUMER_NAME'])
            ->setPactDir($this->config['PACT_OUTPUT_DIR'])
            ->setPactSpecificationVersion(PactConfigInterface::DEFAULT_SPECIFICATION_VERSION);

        return $config;
    }

    public function verifyInteractions(): bool
    {
        foreach ($this->builders as $builder) {
            $builder->verify();
        }

        return true;
    }

    public function registerInteraction(InteractionRequestDTO $requestDTO, InteractionResponseDTO $responseDTO, string $providerState): void
    {
        $providerName = $requestDTO->getProviderName();

        $request  = $this->interactionCompositor->createRequestFromDTO($requestDTO);
        $response = $this->interactionCompositor->createResponseFromDTO($responseDTO);

        $this->builders[$providerName]->newInteraction();
        $this->builders[$providerName]
            ->given($providerState)
            ->uponReceiving($requestDTO->getDescription())
            ->with($request)
            ->willRespondWith($response);
    }

    public function getConsumerVersion(): string
    {
        return (string) $this->config['PACT_CONSUMER_VERSION'];
    }
}
