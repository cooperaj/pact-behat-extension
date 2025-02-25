<?php

declare(strict_types=1);

namespace Tests\SmartGamma\Behat\PactExtension\Context;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Hook\Scope\ScenarioScope;
use Behat\Gherkin\Node\ScenarioInterface as Scenario;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Hook\Scope\AfterSuiteScope;
use Behat\Testwork\Specification\SpecificationIterator;
use Behat\Testwork\Tester\Result\TestResult;
use SmartGamma\Behat\PactExtension\Context\Authenticator;
use SmartGamma\Behat\PactExtension\Context\PactContext;
use SmartGamma\Behat\PactExtension\Exception\InvalidResponseObjectNameFormat;
use SmartGamma\Behat\PactExtension\Exception\NoConsumerRequestDefined;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\InteractionRequestDTO;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\InteractionResponseDTO;
use SmartGamma\Behat\PactExtension\Infrastructure\Pact;
use SmartGamma\Behat\PactExtension\Infrastructure\ProviderState\InjectorStateDTO;
use SmartGamma\Behat\PactExtension\Infrastructure\ProviderState\PlainTextStateDTO;
use SmartGamma\Behat\PactExtension\Infrastructure\ProviderState\ProviderState;
use stdClass;

#[CoversClass(PactContext::class)]
#[UsesClass(InteractionRequestDTO::class)]
#[UsesClass(InteractionResponseDTO::class)]
#[UsesClass(InjectorStateDTO::class)]
#[UsesClass(PlainTextStateDTO::class)]
final class PactContextTest extends TestCase
{
    private MockObject|Pact $pactMock;
    private MockObject|ProviderState $providerStateMock;
    private MockObject|Authenticator $authenticatorMock;
    private PactContext $pactContext;

    const PROVIDER_NAME       = 'provider_name';
    const PROVIDER_STATE_TEXT = 'phpspec provider state';

    protected function setUp(): void
    {
        $this->pactMock = $this->createMock(Pact::class);
        $this->providerStateMock = $this->createMock(ProviderState::class);
        $this->authenticatorMock = $this->createMock(Authenticator::class);

        $this->pactContext = new PactContext();
        $this->pactContext->initialize($this->pactMock, $this->providerStateMock, $this->authenticatorMock);
    }

    #[Test]
    public function registersInteraction(): void
    {
        $this->pactMock->expects($this->once())->method('registerInteraction');

        $this->providerStateMock
            ->expects($this->once())
            ->method('getStateDescription')
            ->with(self::PROVIDER_NAME)
            ->willReturn(self::PROVIDER_STATE_TEXT);

        $this->pactContext->registerInteraction(self::PROVIDER_NAME, 'GET', '/', 200);
    }

    #[Test]
    public function registersInteractionWithBody(): void
    {
        $response = new TableNode([1 => ['val1', 'val2']]);
        $this->pactMock->expects($this->once())->method('registerInteraction');

        $this->providerStateMock
            ->expects($this->once())
            ->method('getStateDescription')
            ->with(self::PROVIDER_NAME)
            ->willReturn(self::PROVIDER_STATE_TEXT);

        $this->pactContext->registerInteractionWithBody(
            self::PROVIDER_NAME,
            'GET',
            '/',
            200,
            $response
        );
    }

    #[Test]
    public function registersInteractionWithComplexBody(): void
    {
        $response = new stdClass();
        $this->pactMock->expects($this->once())->method('registerInteraction');

        $this->providerStateMock
            ->expects($this->once())
            ->method('getStateDescription')
            ->with(self::PROVIDER_NAME)
            ->willReturn(self::PROVIDER_STATE_TEXT);

        $this->pactContext->registerInteractionWithBody(
            self::PROVIDER_NAME,
            'GET',
            '/',
            200,
            $response
        );
    }

    #[Test]
    public function registersInteractionWithQuery(): void
    {
        $this->pactMock->expects($this->once())->method('registerInteraction');

        $this->providerStateMock
            ->expects($this->once())
            ->method('getStateDescription')
            ->with(self::PROVIDER_NAME)
            ->willReturn(self::PROVIDER_STATE_TEXT);

        $this->pactContext->registerInteractionWithQuery(
            self::PROVIDER_NAME,
            'GET',
            '/',
            'filter=1',
            200
        );
    }

    #[Test]
    public function registersInteractionWithQueryAndBody(): void
    {
        $responseTable = new TableNode([1 => ['val1', 'val2']]);
        $this->pactMock->expects($this->once())->method('registerInteraction');

        $this->providerStateMock
            ->expects($this->once())
            ->method('getStateDescription')
            ->with(self::PROVIDER_NAME)
            ->willReturn(self::PROVIDER_STATE_TEXT);

        $this->pactContext->registerInteractionWithQueryAndBody(
            self::PROVIDER_NAME,
            'GET',
            '/',
            'filter=1',
            200,
            $responseTable
        );
    }

    #[Test]
    public function registersInteractionWithQueryAndComplexBody(): void
    {
        $response = new stdClass();
        $this->pactMock->expects($this->once())->method('registerInteraction');

        $this->providerStateMock
            ->expects($this->once())
            ->method('getStateDescription')
            ->with(self::PROVIDER_NAME)
            ->willReturn(self::PROVIDER_STATE_TEXT);

        $this->pactContext->registerInteractionWithQueryAndBody(
            self::PROVIDER_NAME,
            'GET',
            '/',
            'filter=1',
            200,
            $response
        );
    }

    #[Test]
    public function rememberRequestToProviderWithParameters(): void
    {
        $requestTable = new TableNode([1 => ['val1', 'val2']]);

        $this->assertTrue(
            $this->pactContext->requestToWithParameters(
                self::PROVIDER_NAME,
                'GET',
                '/',
                $requestTable
            )
        );
    }

    #[Test]
    public function registersResponseForTheStoredRequest(): void
    {
        $requestTable = new TableNode([1 => ['val1', 'val2']]);
        $this->pactContext->requestToWithParameters(self::PROVIDER_NAME, 'GET', '/', $requestTable);

        $responseTable = new TableNode([1 => ['val1', 'val2']]);
        $this->pactMock->expects($this->once())->method('registerInteraction');

        $this->pactContext->theProviderRequestShouldReturnResponseWithAndBody(
            self::PROVIDER_NAME,
            200,
            $responseTable,
        );
    }

    #[Test]
    public function throwsExceptionWhenRequestNotDefinedForResponse(): void
    {
        $responseTable = new TableNode([1 => ['val1', 'val2']]);
        $this->pactMock->expects($this->never())->method('registerInteraction');

        $this->expectException(NoConsumerRequestDefined::class);
        $this->pactContext->theProviderRequestShouldReturnResponseWithAndBody(
            self::PROVIDER_NAME,
            200,
            $responseTable,
        );
    }

    #[Test]
    public function definesObjectStructureForMatcher(): void
    {
        $table = new TableNode([1 => ['val1', 'val2']]);
        $this->assertTrue(
            $this->pactContext->hasTheFollowingStructureInTheResponse('<objectName>', $table)
        );
    }

    #[Test]
    public function throwsExceptionWhenDefinedResponseStructureNotCorrect(): void
    {
        $table = new TableNode([1 => ['val1', 'val2']]);

        $this->expectException(InvalidResponseObjectNameFormat::class);
        $this->pactContext->hasTheFollowingStructureInTheResponse('objectName', $table);
    }

    #[Test]
    public function startMockServerForApi(): void
    {
        $this->pactMock
            ->expects($this->once())
            ->method('startServer')
            ->with(self::PROVIDER_NAME)
            ->willReturn(1);

        $this->assertEquals(
            1,
            $this->pactContext->mockedApiProviderIsAvailable(self::PROVIDER_NAME)
        );
    }

    #[Test]
    public function registersEntityForTheProviderState(): void
    {
        $this->providerStateMock->expects($this->once())->method('addInjectorState');

        $table = new TableNode([1 => ['val1', 'val2']]);

        $this->pactContext->onTheProvider('MyEntity', self::PROVIDER_NAME, $table);
    }

    #[Test]
    public function registersEntityWithDescriptionForTheProviderState(): void
    {
        $this->providerStateMock->expects($this->once())->method('addInjectorState');

        $table = new TableNode([1 => ['val1', 'val2']]);

        $this->pactContext->onTheProviderWithDescription(
            'MyEntity',
            self::PROVIDER_NAME,
            'Entity description',
            $table
        );
    }

    #[Test]
    public function registersPlainTextProviderState(): void
    {
        $stateText = 'string line';
        $state = new PyStringNode([$stateText], 0);

        $this->providerStateMock
            ->expects($this->once())
            ->method('setPlainTextState')
            ->with(new PlainTextStateDTO(self::PROVIDER_NAME, $stateText));

        $this->pactContext->providerPlainTextState(self::PROVIDER_NAME, $state);
    }

    #[Test]
    public function httpAuthorizeConsumerOnProvider(): void
    {
        $this->authenticatorMock
            ->expects($this->once())
            ->method('authorizeConsumerRequestToProvider')
            ->with('http', 'user:pass');

        $this->pactContext->theConsumerAuthorizedAsOn('http', 'user:pass', self::PROVIDER_NAME);
    }

    /**
     * @param string[] $tags
     * @param bool  $verifyExpected
     *
     * @return void
     * @throws Exception
     */
    #[Test]
    #[TestWith([['pact'], true])]
    #[TestWith([['other', 'tags'], false])]
    public function verifyInteractionWhenAppropriate(array $tags, bool $verifyExpected): void
    {
        $scenarioMock = $this->createMock(Scenario::class);
        $scenarioMock->expects($this->once())->method('getTags')->willReturn($tags);

        $scopeMock = $this->createMock(ScenarioScope::class);
        $scopeMock->expects($this->once())->method('getScenario')->willReturn($scenarioMock);

        $this->pactMock
            ->expects($verifyExpected ? $this->once() : $this->never())
            ->method('verifyInteractions');
        $this->pactMock->expects($this->once())->method('cleanupInteractions')->willReturn(true);

        $this->providerStateMock->expects($this->once())->method('clearStates');

        $this->pactContext->setupBehatTags($scopeMock);
        $this->pactContext->verifyInteractions();
    }

    #[Test]
    public function finalizesPactOnTestsSuccess(): void
    {
        $environmentMock = $this->createMock(Environment::class);
        $iteratorMock    = $this->createMock(SpecificationIterator::class);
        $testResultMock  = $this->createMock(TestResult::class);

        $testResultMock->expects($this->once())->method('isPassed')->willReturn(true);
        $this->pactMock->expects($this->once())->method('finalize');

        $this->pactMock->expects($this->once())->method('getConsumerVersion')->willReturn('1.0.0');

        $this->pactContext->teardown(
            new AfterSuiteScope(
                $environmentMock,
                $iteratorMock,
                $testResultMock
            )
        );
    }

    #[Test]
    public function ignoresFinalizesPactOnTestsFailed(): void
    {
        $environmentMock = $this->createMock(Environment::class);
        $iteratorMock    = $this->createMock(SpecificationIterator::class);
        $testResultMock  = $this->createMock(TestResult::class);

        $testResultMock->expects($this->once())->method('isPassed')->willReturn(false);
        $this->pactMock->expects($this->never())->method('finalize');

        $this->pactContext->teardown(
            new AfterSuiteScope(
                $environmentMock,
                $iteratorMock,
                $testResultMock
            )
        );
    }
}
