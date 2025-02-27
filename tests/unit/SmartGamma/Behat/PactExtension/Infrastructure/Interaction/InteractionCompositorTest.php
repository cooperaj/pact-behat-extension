<?php

declare(strict_types=1);

namespace Tests\SmartGamma\Behat\PactExtension\Infrastructure\Interaction;

use Behat\Gherkin\Node\TableNode;
use PhpPact\Consumer\Model\ConsumerRequest;
use PhpPact\Consumer\Model\ProviderResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\InteractionCompositor;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\InteractionRequestDTO;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\InteractionResponseDTO;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\MatcherInterface;
use stdClass;

#[CoversClass(InteractionCompositor::class)]
#[UsesClass(InteractionRequestDTO::class)]
#[UsesClass(InteractionResponseDTO::class)]
final class InteractionCompositorTest extends TestCase
{
    private InteractionCompositor $interactionCompositor;

    const PROVIDER_NAME= 'some_provider_name';
    const PROVIDER_API_PATH = '/api/test';

    protected function setUp(): void
    {
        $matcherMock                 = $this->createMock(MatcherInterface::class);
        $this->interactionCompositor = new InteractionCompositor($matcherMock);
    }

    #[Test]
    public function createsBasicConsumerRequest(): void
    {
        $dto = new InteractionRequestDTO(
            self::PROVIDER_NAME,
            '',
            self::PROVIDER_API_PATH
        );

        $this->assertInstanceOf(ConsumerRequest::class, $this->interactionCompositor->createRequestFromDTO($dto));
    }

    #[Test]
    public function createsBasicProviderResponse(): void
    {
        $dto = new InteractionResponseDTO(200);

        $this->assertInstanceOf(ProviderResponse::class, $this->interactionCompositor->createResponseFromDTO($dto));
    }

    #[Test]
    public function createsTablenodeBasedProviderResponse(): void
    {
        $response = new TableNode(
            [
                ['parameter', 'value'],
                ['test1', 'test1value'],
                ['test2', '1'],
            ]
        );

        $dto = new InteractionResponseDTO(200, $response->getHash());

        $this->assertInstanceOf(ProviderResponse::class, $this->interactionCompositor->createResponseFromDTO($dto));
    }

    #[Test]
    public function createsTablenodeBasedProviderResponseWithEachLike(): void
    {
        $response = new TableNode(
            [
                ['parameter', 'value', 'match'],
                ['test1', 'test1value', ''],
                ['test2', '1', ''],
                ['test3', '<other>', 'eachLike']
            ]
        );

        $matchingObjects = [
            '<other>' => [
                'test1' => ['test1value', ''],
                'test2' => ['1', ''],
            ]
        ];

        $dto = new InteractionResponseDTO(200, $response->getHash(), $matchingObjects);

        $this->assertInstanceOf(ProviderResponse::class, $this->interactionCompositor->createResponseFromDTO($dto));
    }

    #[Test]
    public function createsTablenodeBasedProviderResponseWithCustomMatcher(): void
    {
        $response = new TableNode(
            [
                ['parameter', 'value', 'match'],
                ['test1', 'test1value', 'like'],
                ['test2', 'true', 'boolean'],
            ]
        );

        $dto = new InteractionResponseDTO(200, $response->getHash());

        $this->assertInstanceOf(ProviderResponse::class, $this->interactionCompositor->createResponseFromDTO($dto));
    }

    #[Test]
    public function createsStdClassBasedProviderResponse(): void
    {
        $response = new stdClass();
        $response->test1 = 'test1Value';
        $response->test2 = true;

        $dto = new InteractionResponseDTO(200, $response);

        $this->assertInstanceOf(ProviderResponse::class, $this->interactionCompositor->createResponseFromDTO($dto));
    }
}
