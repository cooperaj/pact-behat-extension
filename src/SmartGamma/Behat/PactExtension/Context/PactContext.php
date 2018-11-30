<?php

namespace SmartGamma\Behat\PactExtension\Context;

use App\Kernel;
use Behat\Behat\Hook\Call\AfterSuite;
use Behat\Behat\Hook\Call\BeforeStep;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Behat\Testwork\Hook\Scope\AfterSuiteScope;
use PhpPact\Consumer\Model\ConsumerRequest;
use PhpPact\Consumer\Model\ProviderResponse;
use SmartGamma\Behat\PactExtension\Infrastructure\MatcherInterface;
use SmartGamma\Behat\PactExtension\Infrastructure\Pact;
use SmartGamma\Behat\PactExtension\Infrastructure\InteractionCompositor;
use SmartGamma\Behat\PactExtension\Infrastructure\Provider\ProviderRequest;

class PactContext implements PactContextInterface
{
    /**
     * @var string
     */
    private static $stepName;

    /**
     * @var string
     */
    private static $scenarioName;

    /**
     * @var array
     */
    private static $tags = [];

    /**
     * @var string
     */
    private $providerEntityName;

    /**
     * @var array
     */
    private $providerEntityData = [];

    /**
     * @var array
     */
    private $providerEntityDescription = [];

    /**
     * @var MatcherInterface
     */
    private $matcher;

    /**
     * @var array 
     */
    private $providersRequest = [];

    /**
     * @var InteractionCompositor
     */
    private $compositor;

    /**
     * @var Pact
     */
    private static $pact;

    public function initialize(Pact $pact, MatcherInterface $matcher, InteractionCompositor $compositor)
    {
        static::$pact = $pact;
        $this->matcher = $matcher;
        $this->compositor = $compositor;
    }

    /**
     * @BeforeScenario
     */
    public function setupBehatTags(BeforeScenarioScope $scope): void
    {
        static::$tags = $scope->getScenario()->getTags();
    }

    /**
     * @BeforeScenario
     */
    public static function setupBehatStepName(BeforeScenarioScope $step): void
    {
        static::$scenarioName = $step->getScenario()->getTitle();
    }

    /**
     * @BeforeStep
     */
    public static function setupBehatScenarioName(\Behat\Behat\Hook\Scope\BeforeStepScope $step): void
    {
        static::$stepName = $step->getStep()->getText();
    }

    /**
     * @AfterSuite
     */
    public static function teardown(AfterSuiteScope $scope): void
    {
        if (!$scope->getTestResult()->isPassed()) {
            echo 'A test has failed. Skipping PACT file upload.';

            return;
        }

        static::$pact->finalize(Kernel::PACT_CONSUMER_VERSION);
    }

    /**
     * @Given :providerName request :method to :uri should return response with :status
     *
     * @param string $method
     */
    public function registerInteraction(
        string $providerName,
        string $method,
        string $uri,
        int $status
    ): void
    {
        $this->sanitizeProviderName($providerName);
        static::$pact->getBuilder($providerName)
            ->given(static::$scenarioName)
            ->uponReceiving(static::$stepName)
            ->with($this->compositor->createRequest($providerName, $method, $uri))
            ->willRespondWith($this->compositor->createResponse($status));
    }

    /**
     * @Given :providerName request :method to :uri should return response with :status and body:
     *
     * @param string $method
     */
    public function registerInteractionWithBody(
        string $providerName,
        string $method,
        string $uri,
        int $status,
        TableNode $table
    ): void
    {
        $responseBody = $this->parseBodyTable($table);

        $request  = $this->compositor->createRequest($providerName, $method, $uri);
        $response = $this->compositor->createResponse($status, $responseBody);

        $this->sanitizeProviderName($providerName);
        static::$pact->getBuilder($providerName)
            ->given(static::$scenarioName)
            ->uponReceiving(static::$stepName)
            ->with($request)
            ->willRespondWith($response);
    }

    /**
     * @Given :providerName request :method to :uri with :query should return response with :status and body:
     *
     * @param string $method
     */
    public function registerInteractionWithQueryAndBody(
        string $providerName,
        string $method,
        string $uri,
        string $query,
        int $status,
        TableNode $table
    ): void
    {
        $responseBody = $this->parseBodyTable($table);

        $request  = $this->compositor->createRequest($providerName, $method, $uri, $query);
        $response = $this->compositor->createResponse($status, $responseBody);

        $this->sanitizeProviderName($providerName);
        static::$pact->getBuilder($providerName)
            ->given($this->getGivenSection())
            ->uponReceiving(self::$stepName)
            ->with($request)
            ->willRespondWith($response);
    }

    /**
     * @Given :providerName API is available
     */
    public function keeperRegistryIsAvailable(string $providerName): void
    {
        $this->sanitizeProviderName($providerName);
        static::$pact->startServer($providerName);
    }

    /**
     * @AfterScenario
     */
    public function verifyInteractions(): void
    {
        if (\in_array('pact', self::$tags, true)) {
            static::$pact->verifyInteractions();
        }
    }

    /**
     * @Given :entity on the provider:
     */
    public function onTheProvider(string $entity, TableNode $table): bool
    {
        $this->providerEntityName          = $entity;
        $this->providerEntityData[$entity] = \array_slice($table->getRowsHash(), 1);

        return true;
    }

    /**
     * @Given :entity as :description on the provider:
     */
    public function onTheProviderWithDescription($entity, $description, TableNode $table): void
    {
        $this->onTheProvider($entity, $table);
        $this->providerEntityDescription[$entity] = $description ? '(' . $description . ')' : '';
    }

    /**
     * @Given the consumer :authType authorized as :credentials on :providerName
     */
    public function theConsumerAuthorizedAsOn(string $authType, string $credentials, string $providerName): void
    {
        $this->compositor->authorizeConsumerRequestToProvider($authType, $credentials, $providerName);
    }

    /**
     * @Given :providerName request :method to :uri with parameters:
     */
    public function requestToWithParameters(string $providerName, string $method, string $uri, TableNode $table): bool
    {
        $this->sanitizeProviderName( $providerName);

        $this->providersRequest[$providerName] = $this->compositor->createRequest();

        return true;
    }

    /**
     * @Given the :providerName request should return response with :status and body:
     */
    public function theProviderRequestShouldReturnResponseWithAndBody(string $providerName, string $status, TableNode $table)
    {
    }

    private function getGivenSection(): string
    {
        if (\count($this->providerEntityData)) {
            $given = 'Create ' . $this->providerEntityName . $this->providerEntityDescription[$this->providerEntityName] . ':' .
                \json_encode($this->providerEntityData[$this->providerEntityName]);
        } else {
            $given = self::$scenarioName;
        }

        return $given;
    }

    /**
     * @param \Behat\Gherkin\Node\TableNode $tableNode
     *
     * @return mixed
     */
    private function parseBodyTable(TableNode $tableNode)
    {
        return array_reduce(
            $tableNode->getHash(),
            function (array $carry, array $bodyItem) {
                $value = $this->matcher->normolizeValue($bodyItem['value']);

                if (null !== $value) {
                    $carry[$bodyItem['parameter']] = $this->matcher->like($value);
                }

                return $carry;
            },
            []
        );
    }

    private function sanitizeProviderName(string &$name): void
    {
        $name = str_replace(' ', '_', $name);
    }
}
