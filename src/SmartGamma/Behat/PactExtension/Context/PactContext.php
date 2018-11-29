<?php

namespace SmartGamma\Behat\PactExtension\Context;

use App\Kernel;
use Behat\Behat\Hook\Call\AfterSuite;
use Behat\Behat\Hook\Call\BeforeStep;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Behat\Testwork\Hook\Scope\AfterSuiteScope;
use PhpPact\Consumer\Matcher\Matcher;
use PhpPact\Consumer\Model\ConsumerRequest;
use PhpPact\Consumer\Model\ProviderResponse;
use SmartGamma\Behat\PactExtension\Infrastructure\Pact;

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
    private $authHeaders = [];

    /**
     * @var Matcher
     */
    private $matcher;

    /**
     * @var Pact
     */
    private static $pact;

    public function initialize(Pact $pact, Matcher $matcher)
    {
        static::$pact = $pact;
        $this->matcher = $matcher;
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
            ->with($this->createRequest($providerName, $method, $uri))
            ->willRespondWith($this->createResponse($status));
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

        $request  = $this->createRequest($providerName, $method, $uri);
        $response = $this->createResponse($status, $responseBody);

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

        $request  = $this->createRequest($providerName, $method, $uri, $query);
        $response = $this->createResponse($status, $responseBody);

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
    public function onTheProvider($entity, TableNode $table): void
    {
        $this->providerEntityName          = $entity;
        $this->providerEntityData[$entity] = \array_slice($table->getRowsHash(), 1);
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
        $this->authorizeConsumer($authType, $credentials, $providerName);
    }

    private function authorizeConsumer(string $authType, string $credentials, string $providerName): void
    {
        switch ($authType) {
            case 'http':
                $this->authHeaders[$providerName] = ['Authorization' => 'Basic ' . base64_encode($credentials)];
                break;
            default:
                throw new \Exception('No authorization type:' . $authType . ' is supported');
        }
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
     * @param string $method
     * @param string $path
     * @param string $query
     * @param array  $headers
     * @param null   $body
     *
     * @return \PhpPact\Consumer\Model\ConsumerRequest
     */
    private function createRequest(
        string $providerName,
        string $method,
        string $path,
        string $query = null,
        array $headers = [],
        $body = null
    ): ConsumerRequest
    {
        $request = new ConsumerRequest();

        $request
            ->setMethod($method)
            ->setPath($path);

        if (isset($this->authHeaders[$providerName])) {
            $request->setHeaders($this->authHeaders[$providerName]);
        }

        if (null !== $query) {
            $request->setQuery($query);
        }

        foreach ($headers as $key => $value) {
            $request->addHeader($key, $value);
        }

        if (null !== $body) {
            $request->setBody($body);
        }

        return $request;
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
                $value = $this->parseValue($bodyItem['value']);

                if (null !== $value) {
                    $carry[$bodyItem['parameter']] = $this->matcher->like($value);
                }

                return $carry;
            },
            []
        );
    }

    /**
     * @param int        $status
     * @param array|null $bodyParameters
     *
     * @return \PhpPact\Consumer\Model\ProviderResponse
     */
    private function createResponse(int $status, array $bodyParameters = null): ProviderResponse
    {
        $response = new ProviderResponse();
        $response
            ->setStatus($status);

        if (null !== $bodyParameters) {
            $response->setBody($bodyParameters);
        }

        return $response;
    }

    /**
     * @param string $string
     *
     * @return bool|float|int|null|string
     */
    private function parseValue(string $string)
    {
        $string = mb_strtolower(trim($string));

        if (empty($string)) {
            return '';
        }

        if ('null' === $string) {
            return null;
        }

        if (!preg_match('/[^0-9.]+/', $string)) {
            if (preg_match('/[.]+/', $string)) {
                return (float)$string;
            }

            return (int)$string;
        }

        if ('true' === $string) {
            return true;
        }
        if ('false' === $string) {
            return false;
        }

        return (string)$string;
    }

    private function sanitizeProviderName(string &$name): void
    {
        $name = str_replace(' ', '_', $name);
    }
}
