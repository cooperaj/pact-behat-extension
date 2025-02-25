<?php

declare(strict_types=1);

namespace SmartGamma\Behat\PactExtension\Context;

use Behat\Behat\Hook\Scope\StepScope;
use Behat\Behat\Hook\Scope\ScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Hook\AfterScenario;
use Behat\Hook\AfterSuite;
use Behat\Hook\BeforeScenario;
use Behat\Hook\BeforeStep;
use Behat\Step\Given;
use Behat\Testwork\Hook\Scope\AfterSuiteScope;
use SmartGamma\Behat\PactExtension\Exception\InvalidResponseObjectNameFormat;
use SmartGamma\Behat\PactExtension\Exception\NoConsumerRequestDefined;
use SmartGamma\Behat\PactExtension\Infrastructure\ProviderState\InjectorStateDTO;
use SmartGamma\Behat\PactExtension\Infrastructure\ProviderState\ProviderState;
use SmartGamma\Behat\PactExtension\Infrastructure\ProviderState\PlainTextStateDTO;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\InteractionRequestDTO;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\InteractionResponseDTO;
use SmartGamma\Behat\PactExtension\Infrastructure\Pact;

use stdClass;

use function array_slice;
use function in_array;

class PactContext implements PactContextInterface
{
    private static string $stepName;

    /** @var string[] */
    private static array $tags = [];

    private static Pact $pact;

    private static ProviderState $providerState;

    private Authenticator $authenticator;

    /** @var InteractionRequestDTO[] */
    private array $consumerRequest = [];

    /** @var array[] */
    private array $headers = [];

    /** @var array[] */
    private array $matchingObjectStructures = [];

    /**
     * @param Pact          $pact
     * @param ProviderState $providerState
     * @param Authenticator $authenticator
     */
    public function initialize(Pact $pact, ProviderState $providerState, Authenticator $authenticator): void
    {
        self::$pact          = $pact;
        self::$providerState = $providerState;
        $this->authenticator = $authenticator;
        self::$stepName      = __FUNCTION__;
    }

    #[BeforeScenario]
    public function setupBehatTags(ScenarioScope $scope): void
    {
        self::$tags = $scope->getScenario()->getTags();
        self::$providerState->clearStates();
    }

    #[BeforeScenario]
    public static function setupBehatStepName(ScenarioScope $step): void
    {
        if ($step->getScenario()->getTitle()) {
            self::$providerState->setDefaultPlainTextState($step->getScenario()->getTitle());
        }
    }

    #[BeforeStep]
    public static function setupBehatScenarioName(StepScope $step): void
    {
        self::$stepName = $step->getStep()->getText();
    }

    #[Given(':providerName request :method to :uri should return response with :status')]
    public function registerInteraction(
        string $providerName,
        string $method,
        string $uri,
        int $status
    ): void {
        $headers       = $this->getHeaders($providerName);
        $request       = new InteractionRequestDTO($providerName, self::$stepName, $uri, $method, $headers);
        $response      = new InteractionResponseDTO($status);
        $providerState = self::$providerState->getStateDescription($providerName);

        self::$pact->registerInteraction($request, $response, $providerState);
    }

    #[Given(':providerName request :method to :uri should return response with :status and body:')]
    public function registerInteractionWithBody(
        string $providerName,
        string $method,
        string $uri,
        int $status,
        TableNode|stdClass $response
    ): void {
        if ($response instanceof TableNode) {
            $response = $response->getHash();
        }

        $headers       = $this->getHeaders($providerName);
        $requestDTO    = new InteractionRequestDTO($providerName, self::$stepName, $uri, $method, $headers);
        $responseDTO   = new InteractionResponseDTO($status, $response, $this->matchingObjectStructures);
        $providerState = self::$providerState->getStateDescription($providerName);

        self::$pact->registerInteraction($requestDTO, $responseDTO, $providerState);
    }

    #[Given(':providerName request :method to :uri with :query should return response with :status')]
    public function registerInteractionWithQuery(
        string $providerName,
        string $method,
        string $uri,
        string $query,
        int $status
    ): void {
        $headers       = $this->getHeaders($providerName);
        $request       = new InteractionRequestDTO($providerName, self::$stepName, $uri, $method, $headers, $query);
        $response      = new InteractionResponseDTO($status);
        $providerState = self::$providerState->getStateDescription($providerName);

        self::$pact->registerInteraction($request, $response, $providerState);
    }

    #[Given(':providerName request :method to :uri with :query should return response with :status and body:')]
    public function registerInteractionWithQueryAndBody(
        string $providerName,
        string $method,
        string $uri,
        string $query,
        int $status,
        TableNode|stdClass $response
    ): void {
        if ($response instanceof TableNode) {
            $response = $response->getHash();
        }

        $headers       = $this->getHeaders($providerName);
        $requestDTO    = new InteractionRequestDTO($providerName, self::$stepName, $uri, $method, $headers, $query);
        $responseDTO   = new InteractionResponseDTO($status, $response, $this->matchingObjectStructures);
        $providerState = self::$providerState->getStateDescription($providerName);

        self::$pact->registerInteraction($requestDTO, $responseDTO, $providerState);
    }

    #[Given(':providerName request :method to :uri with parameters:')]
    public function requestToWithParameters(
        string $providerName,
        string $method,
        string $uri,
        TableNode $table
    ): bool {
        $headers     = $this->getHeaders($providerName);
        $requestBody = $table->getRowsHash();

        array_shift($requestBody);
        $this->consumerRequest[$providerName] =
            new InteractionRequestDTO($providerName, self::$stepName, $uri, $method, $headers, null, $requestBody);

        return true;
    }

    #[Given('request above to :providerName should return response with :status and body:')]
    public function theProviderRequestShouldReturnResponseWithAndBody(
        string $providerName,
        int $status,
        TableNode|stdClass $response
    ): void {
        if (!isset($this->consumerRequest[$providerName])) {
            throw new NoConsumerRequestDefined('No consumer InteractionRequestDTO defined. Call step: "Given'
                . ' :providerName request :method to :uri with parameters:" before this one.');
        }

        if ($response instanceof TableNode) {
            $response = $response->getHash();
        }

        $request       = $this->consumerRequest[$providerName];
        $response      = new InteractionResponseDTO($status, $response, $this->matchingObjectStructures);
        $providerState = self::$providerState->getStateDescription($providerName);
        unset($this->consumerRequest[$providerName]);

        self::$pact->registerInteraction($request, $response, $providerState);
    }

    #[Given(':object object should have the following structure:')]
    public function hasTheFollowingStructureInTheResponse(string $object, TableNode $table): bool
    {
        if (!preg_match('/^<.*>$/', $object)) {
            throw new InvalidResponseObjectNameFormat(
                'Response object name should be taken in "<...>" like <name>'
            );
        }

        $eachParameters = $table->getRowsHash();
        array_shift($eachParameters);

        $this->matchingObjectStructures[$object] = $eachParameters;

        return true;
    }

    #[Given(':providerName API is available')]
    public function mockedApiProviderIsAvailable(string $providerName): int
    {
        return self::$pact->startServer($providerName);
    }

    #[Given(':entity on the provider :providerName:')]
    public function onTheProvider(string $entity, string $providerName, TableNode $table): void
    {
        $parameters    = array_slice($table->getRowsHash(), 1);
        $injectorState = new InjectorStateDTO($providerName, $entity, $parameters);
        self::$providerState->addInjectorState($injectorState);
    }

    #[Given(':entity as :entityDescription on the provider :providerName:')]
    public function onTheProviderWithDescription(
        string $entity,
        string $providerName,
        string $entityDescription,
        TableNode $table
    ): void {
        $parameters    = array_slice($table->getRowsHash(), 1);
        $injectorState = new InjectorStateDTO($providerName, $entity, $parameters, $entityDescription);
        self::$providerState->addInjectorState($injectorState);
    }

    #[Given('provider :providerName state:')]
    public function providerPlainTextState(string $providerName, PyStringNode $state): void
    {
        $textStateDTO = new PlainTextStateDTO($providerName, $state->getRaw());
        self::$providerState->setPlainTextState($textStateDTO);
    }

    #[Given('the consumer :authType authorized as :credentials on :providerName')]
    public function theConsumerAuthorizedAsOn(string $authType, string $credentials, string $providerName): void
    {
        $this->headers[$providerName] =
            $this->authenticator->authorizeConsumerRequestToProvider($authType, $credentials);
    }

    #[AfterScenario]
    public function verifyInteractions(): void
    {
        try {
            if (in_array('pact', self::$tags, true)) {
                self::$pact->verifyInteractions();
            }
        } finally {
            $result = self::$pact->cleanupInteractions();
            if (!$result) {
                echo 'Failed to cleanup PACT mock server interactions';
            }
        }
    }

    #[AfterSuite]
    public static function teardown(AfterSuiteScope $scope): bool
    {
        if (!$scope->getTestResult()->isPassed()) {
            echo 'A test has failed. Skipping PACT file upload.';
            return false;
        }

        return self::$pact->finalize(self::$pact->getConsumerVersion());
    }

    /**
     * @param string $providerName
     *
     * @return string[]
     */
    private function getHeaders(string $providerName): array
    {
        return $this->headers[$providerName] ?? [];
    }
}
