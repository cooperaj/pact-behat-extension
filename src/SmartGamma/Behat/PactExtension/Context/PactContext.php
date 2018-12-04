<?php

namespace SmartGamma\Behat\PactExtension\Context;

use App\Kernel;
use Behat\Behat\Hook\Call\AfterSuite;
use Behat\Behat\Hook\Call\BeforeStep;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Testwork\Hook\Scope\AfterSuiteScope;
use PhpPact\Consumer\Model\ConsumerRequest;
use PhpPact\Consumer\Model\ProviderResponse;
use SmartGamma\Behat\PactExtension\Exception\NoConsumerRequestDefined;
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
     * @var array 
     */
    private $consumerRequest = [];

    /**
     * @var array
     */
    private $providerTextState = [];

    /**
     * @var InteractionCompositor
     */
    private $compositor;

    /**
     * @var Pact
     */
    private static $pact;

    public function initialize(Pact $pact, InteractionCompositor $compositor)
    {
        static::$pact = $pact;
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
        $request = $this->compositor->createRequest($providerName, $method, $uri);

        $this->sanitizeProviderName($providerName);
        static::$pact->getBuilder($providerName)
            ->given($this->getGivenSection($providerName))
            ->uponReceiving(static::$stepName)
            ->with($request)
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
        $request  = $this->compositor->createRequest($providerName, $method, $uri);
        $response = $this->compositor->createResponse($status, $table->getHash());

        $this->sanitizeProviderName($providerName);
        static::$pact->getBuilder($providerName)
            ->given($this->getGivenSection($providerName))
            ->uponReceiving(static::$stepName)
            ->with($request)
            ->willRespondWith($response);
    }

    /**
     * @Given :providerName request :method to :uri with :query should return response with :status
     *
     * @param string $method
     */
    public function registerInteractionWithQuery(
        string $providerName,
        string $method,
        string $uri,
        string $query,
        int $status
    ): void
    {
        $request  = $this->compositor->createRequest($providerName, $method, $uri, $query);
        $response = $this->compositor->createResponse($status);

        $this->sanitizeProviderName($providerName);
        static::$pact->getBuilder($providerName)
            ->given($this->getGivenSection($providerName))
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
        $request  = $this->compositor->createRequest($providerName, $method, $uri, $query);
        $response = $this->compositor->createResponse($status, $table->getHash());

        $this->sanitizeProviderName($providerName);
        static::$pact->getBuilder($providerName)
            ->given($this->getGivenSection($providerName))
            ->uponReceiving(self::$stepName)
            ->with($request)
            ->willRespondWith($response);
    }

    /**
     * @Given :providerName request :method to :uri with parameters:
     */
    public function requestToWithParameters(string $providerName, string $method, string $uri, TableNode $table): bool
    {
        $requestBody = $table->getRowsHash();
        array_shift($requestBody);

        $this->consumerRequest[$providerName] = $this->compositor->createRequest($providerName, $method, $uri, null, [], $requestBody);

        return true;
    }

    /**
     * @Given request above to :providerName should return response with :status and body:
     */
    public function theProviderRequestShouldReturnResponseWithAndBody(string $providerName, string $status, TableNode $table): bool
    {
        if(false === isset($this->consumerRequest[$providerName])) {
            throw new NoConsumerRequestDefined('No consumer Request defined. Call step: "Given :providerName request :method to :uri with parameters:" before this one.');
        }

        $request = $this->consumerRequest[$providerName];
        $response = $this->compositor->createResponse($status, $table->getHash());

        $this->sanitizeProviderName($providerName);
        static::$pact->getBuilder($providerName)
            ->given($this->getGivenSection($providerName))
            ->uponReceiving(self::$stepName)
            ->with($request)
            ->willRespondWith($response);

        unset($this->consumerRequest[$providerName]);

        return true;
    }

    /**
     * @Given :providerName API is available
     */
    public function keeperRegistryIsAvailable(string $providerName): bool
    {
        $this->sanitizeProviderName($providerName);
        static::$pact->startServer($providerName);

        return true;
    }

    /**
     * @Given :entity on the provider :providerName:
     */
    public function onTheProvider(string $entity, string $providerName, TableNode $table): bool
    {
        $this->sanitizeProviderName( $providerName);
        $this->providerEntityName[$providerName]          = $entity;
        $this->providerEntityData[$providerName][$entity] = \array_slice($table->getRowsHash(), 1);

        return true;
    }

    /**
     * @Given :entity as :description on the provider :providerName:
     */
    public function onTheProviderWithDescription(string $entity, string $providerName, string $description, TableNode $table): void
    {
        $this->sanitizeProviderName( $providerName);
        $this->onTheProvider($entity, $providerName, $table);
        $this->providerEntityDescription[$providerName][$entity] = $description ? '(' . $description . ')' : '';
    }

    /**
     * @Given provider :providerName state:
     */
    public function providerState(string $providerName, PyStringNode $state): void
    {
        $this->sanitizeProviderName($providerName);
        $this->providerTextState[$providerName] = $state->getRaw();
    }

    /**
     * @Given the consumer :authType authorized as :credentials on :providerName
     */
    public function theConsumerAuthorizedAsOn(string $authType, string $credentials, string $providerName): void
    {
        $this->compositor->authorizeConsumerRequestToProvider($authType, $credentials, $providerName);
    }

    private function getGivenSection(string $providerName): string
    {
        if (isset($this->providerEntityData[$providerName]) && sizeof($this->providerEntityData[$providerName][$this->providerEntityName[$providerName]])) {

            $given = 'Create '
                    . $this->providerEntityName[$providerName]
                    . $this->providerEntityDescription[$providerName][$this->providerEntityName[$providerName]]
                    . ':'
                    . \json_encode($this->providerEntityData[$providerName][$this->providerEntityName[$providerName]]);

            return $given;
        }

        if (isset($this->providerTextState[$providerName])) {
            $given = $this->providerTextState[$providerName];

            return $given;
        }

        $given = self::$scenarioName;

        return $given;
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

    private function sanitizeProviderName(string &$name): void
    {
        $name = str_replace(' ', '_', $name);
    }
}
