<?php

namespace SmartGamma\Behat\PactExtension\Infrastructure;

use GuzzleHttp\Psr7\Uri;
use PhpPact\Broker\Service\BrokerHttpClient;
use PhpPact\Consumer\InteractionBuilder;
use PhpPact\Http\GuzzleClient;
use PhpPact\Standalone\MockService\MockServer;
use PhpPact\Standalone\MockService\MockServerConfig;
use PhpPact\Standalone\MockService\MockServerEnvConfig;
use PhpPact\Standalone\MockService\Service\MockServerHttpService;

class Pact
{
    /**
     * @var MockServerFactory
     */
    private $mockServerFactory;

    /**
     * @var array
     */
    private $config                 = [];

    /**
     * @var array
     */
    private $providersConfig        = [];

    /**
     * @var string
     */
    private $tag;

    /**
     * @var array
     */
    private $servers                = [];

    /**
     * @var array
     */
    private $mockServerConfigs      = [];

    /**
     * @var array
     */
    private $mockServerHttpServices = [];

    /**
     * @var array
     */
    private $startedServers         = [];

    /**
     * @var array
     */
    private $builders               = [];

    /**
     * Pact constructor.
     *
     * @param array $config
     * @param array $providersConfig
     */
    public function __construct(MockServerFactory $mockServerFactory, array $config, array $providersConfig)
    {
        $this->mockServerFactory = $mockServerFactory;
        $this->config          = $config;
        $this->providersConfig = $providersConfig;
        $this->tag             = $this->getPactTag();
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
            $this->mockServerHttpServices[$providerName] = new MockServerHttpService(new GuzzleClient(), $mockServerConfig);
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
            $this->builders[$providerName] = new InteractionBuilder($mockServerConfig);
        }
    }

    /**
     * @param array $providerConfig
     *
     * @return MockServerConfig
     */
    private function createMockServerConfig(array $providerConfig): MockServerConfig
    {
        $config = new MockServerConfig();
        $config
            ->setHost($providerConfig['PACT_MOCK_SERVER_HOST'])
            ->setPort($providerConfig['PACT_MOCK_SERVER_PORT'])
            ->setProvider($providerConfig['PACT_PROVIDER_NAME'])
            ->setConsumer($this->config['PACT_CONSUMER_NAME'])
            ->setPactDir($this->config['PACT_OUTPUT_DIR'])
            ->setCors($this->config['PACT_CORS'])
            ->setHealthCheckTimeout($this->config['PACT_MOCK_SERVER_HEALTH_CHECK_TIMEOUT'])
            ->setPactSpecificationVersion(MockServerEnvConfig::DEFAULT_SPECIFICATION_VERSION);

        return $config;
    }

    public function finalize(string $consumerVersion): void
    {
        foreach ($this->mockServerConfigs as $providerName => $mockServerConfig) {
            if (!isset($this->startedServers[$providerName])) {
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
    }

    /**
     * @param string $providerName
     *
     * @return string
     */
    private function getPactJson(string $providerName): string
    {
        $this->mockServerHttpServices[$providerName]->verifyInteractions();

        return $this->mockServerHttpServices[$providerName]->getPactJson();
    }

    /**
     * @param MockServerConfig $config
     * @param string           $json
     * @param string           $consumerVersion
     */
    private function publishToBroker(MockServerConfig $config, string $json, string $consumerVersion): void
    {
        $clientConfig = [];
        if (isset($this->config['PACT_BROKER_HTTP_AUTH_USER'])
            && isset($this->config['PACT_BROKER_HTTP_AUTH_PASS'])) {
            $clientConfig = [
                'auth' => [
                    $this->config['PACT_BROKER_HTTP_AUTH_USER'],
                    $this->config['PACT_BROKER_HTTP_AUTH_PASS'],
                ],
            ];
        }

        $pactBrokerUri     = $this->config['PACT_BROKER_URI'];
        $brokerHttpService = new BrokerHttpClient(new GuzzleClient($clientConfig), new Uri($pactBrokerUri));
        $brokerHttpService->publishJson($json, $consumerVersion);
        $brokerHttpService->tag($config->getConsumer(), $consumerVersion, $this->tag);
        echo 'Pact file has been uploaded to the Broker successfully with version ' . $consumerVersion . ' by tag:' . $tag;
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
        $output = exec('git symbolic-ref HEAD');
        $parts  = explode('/', $output);

        return end($parts);
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

        $pid = $this->servers[$providerName]->start();
        $this->startedServers[$providerName] = $pid;

        return $pid;
    }

    /**
     * @return bool
     */
    public function verifyInteractions(): bool
    {
        foreach ($this->startedServers as $providerName => $val) {
            $this->builders[$providerName]->verify();
            $this->mockServerHttpServices[$providerName]->deleteAllInteractions();
        }

        return true;
    }

    /**
     * @param string $providerName
     *
     * @return InteractionBuilder
     */
    public function getBuilder(string $providerName): InteractionBuilder
    {
        return $this->builders[$providerName];
    }
}
