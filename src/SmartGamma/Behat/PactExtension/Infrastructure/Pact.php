<?php

declare(strict_types=1);

namespace SmartGamma\Behat\PactExtension\Infrastructure;

use GuzzleHttp\Exception\ClientException;
use PhpPact\Consumer\InteractionBuilder;
use PhpPact\Standalone\MockService\MockServer;
use PhpPact\Standalone\MockService\MockServerConfig;
use PhpPact\Standalone\MockService\MockServerEnvConfig;
use PhpPact\Standalone\MockService\Service\MockServerHttpService;
use SmartGamma\Behat\PactExtension\Infrastructure\Factory\BrokerHttpClientFactory;
use SmartGamma\Behat\PactExtension\Infrastructure\Factory\InteractionBuilderFactory;
use SmartGamma\Behat\PactExtension\Infrastructure\Factory\MockServerFactory;
use SmartGamma\Behat\PactExtension\Infrastructure\Factory\MockServerHttpServiceFactory;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\InteractionCompositor;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\InteractionRequestDTO;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\InteractionResponseDTO;

class Pact
{
    private MockServerFactory $mockServerFactory;
    private InteractionBuilderFactory $interactionBuilderFactory;
    private MockServerHttpServiceFactory $mockServerHttpServiceFactory;
    private InteractionCompositor $interactionCompositor;
    private BrokerHttpClientFactory $brokerHttpClientFactory;

    private array $config = [];
    private array $providersConfig = [];
    private string $tag;

    /** @var MockServer[] $servers */
    private array $servers = [];

    /** @var int [] $startedServers */
    private array $startedServers = [];

    /** @var MockServerConfig[] $mockServerConfigs */
    private array $mockServerConfigs = [];

    /** @var MockServerHttpService[] $mockServerHttpServices */
    private array $mockServerHttpServices = [];

    /** @var InteractionBuilder[] $builders */
    private array $builders = [];

    public function __construct(
        MockServerFactory $mockServerFactory,
        InteractionBuilderFactory $interactionBuilderFactory,
        MockServerHttpServiceFactory $mockServerHttpServiceFactory,
        InteractionCompositor $interactionCompositor,
        BrokerHttpClientFactory $brokerHttpClientFactory,
        array $config,
        array $providersConfig
    ) {
        $this->mockServerFactory            = $mockServerFactory;
        $this->interactionBuilderFactory    = $interactionBuilderFactory;
        $this->mockServerHttpServiceFactory = $mockServerHttpServiceFactory;
        $this->interactionCompositor        = $interactionCompositor;
        $this->brokerHttpClientFactory      = $brokerHttpClientFactory;
        $this->config                       = $config;
        $this->providersConfig              = $providersConfig;
        $this->tag                          = $this->getPactTag();
        $this->registerMockServerConfigs();
        $this->registerMockServerHttpServices();
        $this->registerServers();
        $this->registerBuilders();
    }


    private function registerMockServerConfigs(): void
    {
        foreach ($this->providersConfig as $providerName => $providerConfig) {
            $this->mockServerConfigs[$providerName] = $this->createMockServerConfig($providerConfig);
        }
    }

    private function registerMockServerHttpServices(): void
    {
        foreach ($this->mockServerConfigs as $providerName => $mockServerConfig) {
            $this->mockServerHttpServices[$providerName] = $this->mockServerHttpServiceFactory->create($mockServerConfig);
        }
    }

    private function registerServers(): void
    {
        foreach ($this->mockServerConfigs as $providerName => $mockServerConfig) {
            $this->servers[$providerName] = $this->mockServerFactory->create($mockServerConfig);
        }
    }


    private function registerBuilders(): void
    {
        foreach ($this->mockServerConfigs as $providerName => $mockServerConfig) {
            $this->builders[$providerName] = $this->interactionBuilderFactory->create($mockServerConfig);
        }
    }

    private function createMockServerConfig(array $providerConfig): MockServerConfig
    {
        $config = new MockServerConfig();
        $config
            ->setHost($providerConfig['PACT_MOCK_SERVER_HOST'])
            ->setPort(intval($providerConfig['PACT_MOCK_SERVER_PORT']))
            ->setProvider($providerConfig['PACT_PROVIDER_NAME'])
            ->setConsumer($this->config['PACT_CONSUMER_NAME'])
            ->setPactDir($this->config['PACT_OUTPUT_DIR'])
            ->setCors($this->config['PACT_CORS'])
            ->setHealthCheckTimeout($this->config['PACT_MOCK_SERVER_HEALTH_CHECK_TIMEOUT'])
            ->setPactSpecificationVersion(MockServerEnvConfig::DEFAULT_SPECIFICATION_VERSION);

        return $config;
    }

    /**
     * @param string $consumerVersion
     * @param bool $externalMockService Set to true when running an external mocking service
     *
     * @return bool
     */
    public function finalize(string $consumerVersion, bool $externalMockService = false): bool
    {
        foreach ($this->mockServerConfigs as $providerName => $mockServerConfig) {
            if (!$externalMockService && !isset($this->startedServers[$providerName])) {
                echo 'Ignoring ' . $providerName . ' as it was not started in the suite';
                continue;
            }

            if (!isset($this->config['PACT_BROKER_URI'])) {
                echo 'PACT_BROKER_URI environment variable was not set. Skipping PACT file upload for:' . $providerName;
                continue;
            }

            $json = $this->getPactJson($providerName);
            $this->publishToBroker($mockServerConfig, $json, $consumerVersion);
        }

        return true;
    }

    private function getPactJson(string $providerName): string
    {
        $this->mockServerHttpServices[$providerName]->verifyInteractions();

        return $this->mockServerHttpServices[$providerName]->getPactJson();
    }

    private function publishToBroker(MockServerConfig $config, string $json, string $consumerVersion): void
    {
        $brokerHttpService = $this->brokerHttpClientFactory->create();

        try {
            $brokerHttpService->publishJson($json, $consumerVersion);
            $brokerHttpService->tag($config->getConsumer(), $consumerVersion, $this->tag);
            echo 'Pact file has been uploaded to the Broker successfully with version ' . $consumerVersion . ' by tag:'
                . $this->tag;
        } catch (ClientException $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    private function getPactTag(): string
    {
        if (!($tag = \getenv('PACT_CONSUMER_TAG'))) {
            $tag = $this->resolvePactTag($this->getCurrentGitBranch());
        }

        return $tag;
    }

    private function getCurrentGitBranch(): string
    {
        $branch = 'none';
        if (is_dir(__DIR__ . '/.git')) {
            $output = exec('git symbolic-ref HEAD');
            $parts  = explode('/', $output);
            $branch = end($parts);
        }

        return $branch;
    }

    private function resolvePactTag(string $branch)
    {
        return \in_array($branch, ['develop', 'master'], true) ? 'master' : $branch;
    }

    public function startServer(string $providerName): int
    {
        if (isset($this->startedServers[$providerName])) {
            return $this->startedServers[$providerName];
        }

        $pid = $this->startedServers[$providerName] = $this->servers[$providerName]->start();

        return $pid;
    }

    public function verifyInteractions(): bool
    {
        foreach ($this->mockServerHttpServices as $providerName => $val) {
            $this->builders[$providerName]->verify();
        }

        return true;
    }

    public function cleanupInteractions(): bool
    {
        foreach ($this->mockServerHttpServices as $providerName => $val) {
            $this->mockServerHttpServices[$providerName]->deleteAllInteractions();
        }

        return true;
    }

    public function registerInteraction(InteractionRequestDTO $requestDTO, InteractionResponseDTO $responseDTO, string $providerState): void
    {
        $providerName = $requestDTO->getProviderName();

        $request  = $this->interactionCompositor->createRequestFromDTO($requestDTO);
        $response = $this->interactionCompositor->createResponseFromDTO($responseDTO);

        $this->builders[$providerName]
            ->given($providerState)
            ->uponReceiving($requestDTO->getDescription())
            ->with($request)
            ->willRespondWith($response);
    }

    public function getConsumerVersion(): string
    {
        return $this->config['PACT_CONSUMER_VERSION'];
    }
}
