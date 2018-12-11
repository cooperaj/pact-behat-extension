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
use SmartGamma\Behat\PactExtension\Infrastructure\InteractionRequestDTO;
use SmartGamma\Behat\PactExtension\Infrastructure\InteractionResponseDTO;
use SmartGamma\Behat\PactExtension\Infrastructure\MatcherInterface;
use SmartGamma\Behat\PactExtension\Infrastructure\Pact;
use SmartGamma\Behat\PactExtension\Infrastructure\InteractionCompositor;
use SmartGamma\Behat\PactExtension\Infrastructure\Provider\ProviderRequest;
use SmartGamma\Behat\PactExtension\Infrastructure\ProviderStateDTO;

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

    /**
     * @param Pact                  $pact
     * @param InteractionCompositor $compositor
     */
    public function initialize(Pact $pact, InteractionCompositor $compositor)
    {
        static::$pact     = $pact;
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
     */
    public function registerInteraction(
        string $providerName,
        string $method,
        string $uri,
        int $status
    ): bool
    {
        $this->sanitizeProviderName($providerName);
        $request = new InteractionRequestDTO($providerName, static::$stepName, $uri, $method);
        $response = new InteractionResponseDTO($status);
        $providerState = $this->getGivenSection($providerName);

        return self::$pact->registerInteraction($request, $response, $providerState);
    }

    /**
     * @Given :providerName request :method to :uri should return response with :status and body:
     */
    public function registerInteractionWithBody(
        string $providerName,
        string $method,
        string $uri,
        int $status,
        TableNode $responseTable
    ): bool
    {
        $this->sanitizeProviderName($providerName);
        $request = new InteractionRequestDTO($providerName, static::$stepName, $uri, $method);
        $response = new InteractionResponseDTO($status, $responseTable->getHash());
        $providerState = $this->getGivenSection($providerName);

        return self::$pact->registerInteraction($request, $response, $providerState);
    }

    /**
     * @Given :providerName request :method to :uri with :query should return response with :status
     */
    public function registerInteractionWithQuery(
        string $providerName,
        string $method,
        string $uri,
        string $query,
        int $status
    ): bool
    {
        $this->sanitizeProviderName($providerName);
        $request = new InteractionRequestDTO($providerName, static::$stepName, $uri, $method, $query);
        $response = new InteractionResponseDTO($status);
        $providerState = $this->getGivenSection($providerName);

        return self::$pact->registerInteraction($request, $response, $providerState);
    }

    /**
     * @Given :providerName request :method to :uri with :query should return response with :status and body:
     */
    public function registerInteractionWithQueryAndBody(
        string $providerName,
        string $method,
        string $uri,
        string $query,
        int $status,
        TableNode $responseTable
    ): bool
    {
        $this->sanitizeProviderName($providerName);
        $request = new InteractionRequestDTO($providerName, static::$stepName, $uri, $method, $query);
        $response = new InteractionResponseDTO($status, $responseTable->getHash());
        $providerState = $this->getGivenSection($providerName);

        return self::$pact->registerInteraction($request, $response, $providerState);
    }

    /**
     * @Given :providerName request :method to :uri with parameters:
     */
    public function requestToWithParameters(
        string $providerName,
        string $method,
        string $uri,
        TableNode $table
    ): bool
    {
        $this->sanitizeProviderName($providerName);
        $requestBody = $table->getRowsHash();
        array_shift($requestBody);
        $this->consumerRequest[$providerName] = new InteractionRequestDTO($providerName, static::$stepName, $uri, $method, null, [], $requestBody);

        return true;
    }

    /**
     * @Given request above to :providerName should return response with :status and body:
     */
    public function theProviderRequestShouldReturnResponseWithAndBody(
        string $providerName,
        string $status,
        TableNode $responseTable
    ): bool
    {
        if (false === isset($this->consumerRequest[$providerName])) {
            throw new NoConsumerRequestDefined('No consumer InteractionRequestDTO defined. Call step: "Given :providerName request :method to :uri with parameters:" before this one.');
        }

        $this->sanitizeProviderName($providerName);
        $request  = $this->consumerRequest[$providerName];
        $response = new InteractionResponseDTO($status, $responseTable->getHash());
        $providerState = $this->getGivenSection($providerName);

        unset($this->consumerRequest[$providerName]);

        return self::$pact->registerInteraction($request, $response, $providerState);
    }

    /**
     * @Given :object object should have follow structure:
     */
    public function hasFollowStructureInTheResponseAbove($object, TableNode $table)
    {
        if(false == preg_match('/^<.*>$/', $object)) {
            throw new \InvalidResponseObjectNameFormat('Response object name should be taken in "<...>" like <name>');
        }

        $eachParameters = $table->getRowsHash();
        array_shift($eachParameters);

        $this->compositor->addMatchingStructure($object, $eachParameters);
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
        $this->sanitizeProviderName($providerName);

        $this->providerEntityName[$providerName]          = $entity;
        $this->providerEntityData[$providerName][$entity] = \array_slice($table->getRowsHash(), 1);

        $parameters = \array_slice($table->getRowsHash(), 1);

        //$state = new ProviderInjectorStateDTO($providerName, $entity, $parameters);

       // static::$pact->registerProviderState($state);

        return true;
    }

    /**
     * @Given :entity as :description on the provider :providerName:
     */
    public function onTheProviderWithDescription(string $entity, string $providerName, string $description, TableNode $table): void
    {
        $this->sanitizeProviderName($providerName);
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