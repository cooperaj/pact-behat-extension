<?php

declare(strict_types=1);

namespace spec\SmartGamma\Behat\PactExtension\Context;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Hook\Scope\ScenarioScope;
use Behat\Gherkin\Node\ScenarioInterface as Scenario;
use Behat\Testwork\Hook\Scope\AfterTestScope;
use Behat\Testwork\Tester\Result\TestResult;
use SmartGamma\Behat\PactExtension\Context\Authenticator;
use SmartGamma\Behat\PactExtension\Context\PactContext;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use SmartGamma\Behat\PactExtension\Exception\InvalidResponseObjectNameFormat;
use SmartGamma\Behat\PactExtension\Exception\NoConsumerRequestDefined;
use SmartGamma\Behat\PactExtension\Infrastructure\Pact;
use SmartGamma\Behat\PactExtension\Infrastructure\ProviderState\PlainTextStateDTO;
use SmartGamma\Behat\PactExtension\Infrastructure\ProviderState\ProviderState;
use stdClass;

class PactContextSpec extends ObjectBehavior
{
    const PROVIDER_NAME       = 'provider_name';
    const PROVIDER_STATE_TEXT = 'phpspec provider state';

    public function let(Pact $pact, ProviderState $providerState, Authenticator $authenticator)
    {
        $providerState->getStateDescription(self::PROVIDER_NAME)->willReturn(self::PROVIDER_STATE_TEXT);
        $providerState->clearStates()->willReturn(null);
        $pact->getConsumerVersion()->willReturn('1.0.0');

        $this->initialize($pact, $providerState, $authenticator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PactContext::class);
    }

    public function it_registers_interaction(Pact $pact)
    {
        $pact->registerInteraction(Argument::any(), Argument::any(), Argument::any())->shouldBeCalled();
        $this->registerInteraction(self::PROVIDER_NAME, 'GET', '/', 200);
    }

    public function it_registers_interaction_with_body(Pact $pact)
    {
        $response = new TableNode([1 => ['val1', 'val2']]);
        $pact->registerInteraction(Argument::any(), Argument::any(), Argument::any())->shouldBeCalled();
        $this->registerInteractionWithBody(
            self::PROVIDER_NAME,
            'GET',
            '/',
            200,
            $response
        );
    }

    public function it_registers_interaction_with_complex_body(Pact $pact)
    {
        $response = new stdClass();
        $pact->registerInteraction(Argument::any(), Argument::any(), Argument::any())->shouldBeCalled();
        $this->registerInteractionWithBody(
            self::PROVIDER_NAME,
            'GET',
            '/',
            200,
            $response
        );
    }

    public function it_registers_interaction_with_query(Pact $pact)
    {
        $pact->registerInteraction(Argument::any(), Argument::any(), Argument::any())->shouldBeCalled();
        $this->registerInteractionWithQuery(
            self::PROVIDER_NAME,
            'GET',
            '/',
            'filter=1',
            200
        );
    }

    public function it_registers_interaction_with_query_and_body(Pact $pact)
    {
        $responseTable = new TableNode([1 => ['val1', 'val2']]);
        $pact->registerInteraction(Argument::any(), Argument::any(), Argument::any())->shouldBeCalled();
        $this->registerInteractionWithQueryAndBody(
            self::PROVIDER_NAME,
            'GET',
            '/',
            'filter=1',
            200,
            $responseTable
        );
    }

    public function it_registers_interaction_with_query_and_complex_body(Pact $pact)
    {
        $response = new stdClass();
        $pact->registerInteraction(Argument::any(), Argument::any(), Argument::any())->shouldBeCalled();
        $this->registerInteractionWithQueryAndBody(
            self::PROVIDER_NAME,
            'GET',
            '/',
            'filter=1',
            200,
            $response
        );
    }

    public function it_remember_request_to_provider_with_parameters()
    {
        $requestTable = new TableNode([1 => ['val1', 'val2']]);
        $this->requestToWithParameters(self::PROVIDER_NAME, 'GET', '/', $requestTable)
            ->shouldBe(true);
    }

    public function it_registers_response_for_the_stored_request(Pact $pact)
    {
        $requestTable = new TableNode([1 => ['val1', 'val2']]);
        $this->requestToWithParameters(self::PROVIDER_NAME, 'GET', '/', $requestTable);

        $responseTable = new TableNode([1 => ['val1', 'val2']]);
        $pact->registerInteraction(Argument::any(), Argument::any(), Argument::any())->shouldBeCalled();

        $this->theProviderRequestShouldReturnResponseWithAndBody(
            self::PROVIDER_NAME,
            200,
            $responseTable
        );
    }

    public function it_requires_request_be_defined_before_register_response_to_complex_request(Pact $pact)
    {
        $responseTable = new TableNode([1 => ['val1', 'val2']]);

        $this->shouldThrow(
            new NoConsumerRequestDefined(
                'No consumer InteractionRequestDTO defined. Call step: "Given :providerName request :method '
                . 'to :uri with parameters:" before this one.'
            )
        )->during(
            'theProviderRequestShouldReturnResponseWithAndBody',
            [
                self::PROVIDER_NAME,
                200,
                $responseTable]
        );
    }

    public function it_defines_object_structure_for_matcher()
    {
        $table = new TableNode([1 => ['val1', 'val2']]);
        $this->hasFollowStructureInTheResponseAbove('<objectName>', $table)->shouldBe(true);
    }

    public function it_requires_proper_object_name_for_the_structure_definition()
    {
        $table = new TableNode([1 => ['val1', 'val2']]);
        $this->shouldThrow(
            new InvalidResponseObjectNameFormat('Response object name should be taken in "<...>" like <name>')
        )->during('hasFollowStructureInTheResponseAbove', ['objectName', $table]);
    }

    public function it_start_mock_server_for_api(Pact $pact)
    {
        $pact->startServer(self::PROVIDER_NAME)->shouldBeCalled();
        $this->mockedApiProviderIsAvailable(self::PROVIDER_NAME)->shouldBeInt();
    }

    public function it_registers_entity_for_the_provider_state(ProviderState $providerState)
    {
        $providerState->addInjectorState(Argument::any())->shouldBeCalled();
        $table = new TableNode([1 => ['val1', 'val2']]);
        $this->onTheProvider('MyEntity', self::PROVIDER_NAME, $table);
    }

    public function it_registers_entity_with_description_for_the_provider_state(ProviderState $providerState)
    {
        $providerState->addInjectorState(Argument::any())->shouldBeCalled();
        $table = new TableNode([1 => ['val1', 'val2']]);
        $this->onTheProviderWithDescription(
            'MyEntity',
            self::PROVIDER_NAME,
            'Entity description',
            $table
        );
    }

    public function it_registers_plain_text_provider_state(ProviderState $providerState)
    {
        $stateText = 'string line';
        $state = new PyStringNode([$stateText], 0);
        $providerState->setPlainTextState(new PlainTextStateDTO(self::PROVIDER_NAME, $stateText))
            ->shouldBeCalled();
        $this->providerPlainTextState(self::PROVIDER_NAME, $state);
    }

    public function it_http_authorize_consumer_on_provider(Authenticator $authenticator)
    {
        $authenticator->authorizeConsumerRequestToProvider('http', 'user:pass')->shouldBeCalled();
        $this->theConsumerAuthorizedAsOn('http', 'user:pass', self::PROVIDER_NAME);
    }

    public function it_skip_verify_interaction_when_pact_tag_missed(Pact $pact)
    {
        $pact->verifyInteractions()->shouldNotBeCalled();
        $this->verifyInteractions();
    }

    public function it_verify_interaction_when_pact_tag_added(Pact $pact, ScenarioScope $scope, Scenario $scenario)
    {
        $scenario->getTags()->willReturn(['pact']);
        $scope->getScenario()->willReturn($scenario);
        $this->setupBehatTags($scope);

        $pact->verifyInteractions()->shouldBeCalled();
        $this->verifyInteractions();
    }

    public function it_fanalizes_pact_on_tests_success(Pact $pact, AfterTestScope $scope, TestResult $testResult)
    {
        $testResult->isPassed()->willReturn(true);
        $scope->getTestResult()->willReturn($testResult);
        $pact->finalize(Argument::any())->shouldBeCalled();
        $this->teardown($scope);
    }

    public function it_ignores_fanalizes_pact_on_tests_failed(Pact $pact, AfterTestScope $scope, TestResult $testResult)
    {
        $testResult->isPassed()->willReturn(false);
        $scope->getTestResult()->willReturn($testResult);
        $pact->finalize(Argument::any())->shouldNotBeCalled();
        $this->teardown($scope);
    }
}
