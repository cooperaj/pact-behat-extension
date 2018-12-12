<?php

namespace SmartGamma\Behat\PactExtension\Context;

use App\Kernel;
use Behat\Behat\Hook\Scope\StepScope;
use Behat\Behat\Hook\Scope\ScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Testwork\Hook\Scope\AfterTestScope;
use SmartGamma\Behat\PactExtension\Exception\InvalidResponseObjectNameFormat;
use SmartGamma\Behat\PactExtension\Exception\NoConsumerRequestDefined;
use SmartGamma\Behat\PactExtension\Infrastructure\ProviderState\InjectorStateDTO;
use SmartGamma\Behat\PactExtension\Infrastructure\ProviderState\ProviderState;
use SmartGamma\Behat\PactExtension\Infrastructure\ProviderState\PlainTextStateDTO;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\InteractionRequestDTO;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\InteractionResponseDTO;
use SmartGamma\Behat\PactExtension\Infrastructure\Pact;
use Behat\Gherkin\Node\ArgumentInterface;

class PactContext implements PactContextInterface
{
    /**
     * @var string
     */
    private static $stepName;

    /**
     * @var array
     */
    private static $tags = [];

    /**
     * @var Pact
     */
    private static $pact;

    /**
     * @var ProviderState
     */
    private static $providerState;

    /**
     * @var array
     */
    private $consumerRequest = [];

    /**
     * @var array
     */
    private $headers = [];

    /**
     * @var Authenticator
     */
    private $authenticator;

    /**
     * @var array
     */
    private $matchingObjectStructures = [];

    /**
     * @param Pact          $pact
     * @param ProviderState $providerState
     * @param Authenticator $authenticator
     *
     * @return bool
     */
    public function initialize(Pact $pact, ProviderState $providerState, Authenticator $authenticator): void
    {
        static::$pact          = $pact;
        static::$providerState = $providerState;
        $this->authenticator   = $authenticator;
        static::$stepName = __FUNCTION__;
    }

    /**
     * @BeforeScenario
     */
    public function setupBehatTags(ScenarioScope $scope): void
    {
        static::$tags = $scope->getScenario()->getTags();
        static::$providerState->clearStates();
    }

    /**
     * @BeforeScenario
     */
    public static function setupBehatStepName(ScenarioScope $step): void
    {
        static::$providerState->setDefaultPlainTextState($step->getScenario()->getTitle());
    }

    /**
     * @BeforeStep
     */
    public static function setupBehatScenarioName(StepScope $step): void
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
    ): void
    {
        $headers = $this->getHeaders($providerName);
        $request       = new InteractionRequestDTO($providerName, static::$stepName, $uri, $method, $headers);
        $response      = new InteractionResponseDTO($status);
        $providerState = static::$providerState->getStateDescription($providerName);

        self::$pact->registerInteraction($request, $response, $providerState);
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
    ): void
    {
        $headers = $this->getHeaders($providerName);
        $request       = new InteractionRequestDTO($providerName, static::$stepName, $uri, $method, $headers);
        $response      = new InteractionResponseDTO($status, $responseTable->getHash(), $this->matchingObjectStructures);
        $providerState = static::$providerState->getStateDescription($providerName);

        self::$pact->registerInteraction($request, $response, $providerState);
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
    ): void
    {
        $headers = $this->getHeaders($providerName);
        $request       = new InteractionRequestDTO($providerName, static::$stepName, $uri, $method, $headers, $query);
        $response      = new InteractionResponseDTO($status);
        $providerState = static::$providerState->getStateDescription($providerName);

        self::$pact->registerInteraction($request, $response, $providerState);
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
    ): void
    {
        $headers = $this->getHeaders($providerName);
        $request       = new InteractionRequestDTO($providerName, static::$stepName, $uri, $method, $headers, $query);
        $response      = new InteractionResponseDTO($status, $responseTable->getHash(), $this->matchingObjectStructures);
        $providerState = static::$providerState->getStateDescription($providerName);

        self::$pact->registerInteraction($request, $response, $providerState);
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
        $headers = $this->getHeaders($providerName);
        $requestBody = $table->getRowsHash();
        array_shift($requestBody);
        $this->consumerRequest[$providerName] = new InteractionRequestDTO($providerName, static::$stepName, $uri, $method, $headers, null, $requestBody);

        return true;
    }

    /**
     * @Given request above to :providerName should return response with :status and body:
     */
    public function theProviderRequestShouldReturnResponseWithAndBody(
        string $providerName,
        string $status,
        TableNode $responseTable
    ): void
    {
        if (false === isset($this->consumerRequest[$providerName])) {
            throw new NoConsumerRequestDefined('No consumer InteractionRequestDTO defined. Call step: "Given :providerName request :method to :uri with parameters:" before this one.');
        }

        $request       = $this->consumerRequest[$providerName];
        $response      = new InteractionResponseDTO($status, $responseTable->getHash(), $this->matchingObjectStructures);
        $providerState = static::$providerState->getStateDescription($providerName);
        unset($this->consumerRequest[$providerName]);

        self::$pact->registerInteraction($request, $response, $providerState);
    }

    /**
     * @Given :object object should have follow structure:
     */
    public function hasFollowStructureInTheResponseAbove($object, TableNode $table): bool
    {
        if (false == preg_match('/^<.*>$/', $object)) {
            throw new InvalidResponseObjectNameFormat('Response object name should be taken in "<...>" like <name>');
        }

        $eachParameters = $table->getRowsHash();
        array_shift($eachParameters);

        $this->matchingObjectStructures[$object] = $eachParameters;

        return true;
    }

    /**
     * @Given :providerName API is available
     */
    public function mockedApiProviderIsAvailable(string $providerName): int
    {
        return static::$pact->startServer($providerName);
    }

    /**
     * @Given :entity on the provider :providerName:
     */
    public function onTheProvider(string $entity, string $providerName, TableNode $table): void
    {
        $parameters    = \array_slice($table->getRowsHash(), 1);
        $injectorState = new InjectorStateDTO($providerName, $entity, $parameters);
        static::$providerState->addInjectorState($injectorState);
    }

    /**
     * @Given :entity as :entityDescription on the provider :providerName:
     */
    public function onTheProviderWithDescription(string $entity, string $providerName, string $entityDescription, TableNode $table): void
    {
        $parameters    = \array_slice($table->getRowsHash(), 1);
        $injectorState = new InjectorStateDTO($providerName, $entity, $parameters, $entityDescription);
        static::$providerState->addInjectorState($injectorState);
    }

    /**
     * @Given provider :providerName state:
     */
    public function providerPlainTextState(string $providerName, PyStringNode $state): void
    {
        $textStateDTO = new PlainTextStateDTO($providerName, $state->getRaw());
        static::$providerState->setPlainTextState($textStateDTO);
    }

    /**
     * @Given the consumer :authType authorized as :credentials on :providerName
     */
    public function theConsumerAuthorizedAsOn(string $authType, string $credentials, string $providerName): void
    {
        $this->headers[$providerName] = $this->authenticator->authorizeConsumerRequestToProvider($authType, $credentials);
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
    public static function teardown(AfterTestScope $scope): bool
    {
        if (!$scope->getTestResult()->isPassed()) {
            echo 'A test has failed. Skipping PACT file upload.';

            return false;
        }

        return static::$pact->finalize(self::$pact->getConsumerVersion());
    }

    private function getHeaders(string $providerName): array
    {
        return isset($this->headers[$providerName]) ? $this->headers[$providerName] : [];
    }
}
