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
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\InteractionDTO;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\InteractionRequestDTO;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\InteractionResponseDTO;

/**
 * @phpstan-import-type CommonConfiguration from Extension
 * @phpstan-import-type ProviderConfiguration from Extension
 */
class Pact
{
    /** @var MockServerConfigInterface[] $mockServerConfigs */
    private array $mockServerConfigs = [];

    /** @var InteractionBuilder[] $builders */
    private array $builders = [];

    /** @var array<string, InteractionDTO[]> */
    private array $bufferedInteractions = [];

    /**
     * @param InteractionBuilderFactory     $interactionBuilderFactory
     * @param InteractionCompositor         $interactionCompositor
     * @param array                         $config
     * @param array                         $providersConfig
     * @phpstan-param CommonConfiguration   $config
     * @phpstan-param ProviderConfiguration $providersConfig
     */
    public function __construct(
        private readonly InteractionBuilderFactory $interactionBuilderFactory,
        private readonly InteractionCompositor $interactionCompositor,
        private readonly array $config,
        private readonly array $providersConfig,
    ) {
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
        foreach ($this->bufferedInteractions as $providerName => $interactions) {
            $this->builders[$providerName]->verify();
            unset($this->bufferedInteractions[$providerName]);
        }

        return true;
    }

    public function registerInteraction(
        InteractionRequestDTO $requestDTO,
        InteractionResponseDTO $responseDTO,
        string $providerState,
        bool $finalInteraction = true,
    ): void {
        $providerName = $requestDTO->getProviderName();

        $request  = $this->interactionCompositor->createRequestFromDTO($requestDTO);
        $response = $this->interactionCompositor->createResponseFromDTO($responseDTO);

        $this->builders[$providerName]->newInteraction();
        $this->builders[$providerName]
            ->given($providerState)
            ->uponReceiving($requestDTO->getDescription())
            ->with($request)
            ->willRespondWith($response, $finalInteraction);

        // We tell the verification code to verify this builder
        if (!isset($this->bufferedInteractions[$requestDTO->getProviderName()])) {
            $this->bufferedInteractions[$requestDTO->getProviderName()] = [];
        }
    }

    public function bufferInteraction(
        InteractionRequestDTO $requestDTO,
        InteractionResponseDTO $responseDTO,
        string $providerState,
    ): void {
        if (!isset($this->bufferedInteractions[$requestDTO->getProviderName()])) {
            $this->bufferedInteractions[$requestDTO->getProviderName()] = [];
        }

        $this->bufferedInteractions[$requestDTO->getProviderName()][] = new InteractionDTO(
            requestDTO:    $requestDTO,
            responseDTO:   $responseDTO,
            providerState: $providerState,
        );
    }

    public function registerInteractions(): void
    {
        foreach ($this->bufferedInteractions as $providerName => $interactions) {
            $count = count($interactions);

            foreach ($interactions as $interaction) {
                $this->registerInteraction(
                    $interaction->getRequestDTO(),
                    $interaction->getResponseDTO(),
                    $interaction->getProviderState(),
                    $count <= 1,
                );

                $count--;
            }
        }
    }

    public function getConsumerVersion(): string
    {
        return (string) $this->config['PACT_CONSUMER_VERSION'];
    }
}
