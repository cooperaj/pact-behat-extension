<?php

declare(strict_types=1);

namespace spec\SmartGamma\Behat\PactExtension\Infrastructure\Interaction;

use Behat\Gherkin\Node\TableNode;
use PhpPact\Consumer\Model\ConsumerRequest;
use PhpPact\Consumer\Model\ProviderResponse;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\InteractionCompositor;
use PhpSpec\ObjectBehavior;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\InteractionRequestDTO;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\InteractionResponseDTO;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\MatcherInterface;
use stdClass;

class InteractionCompositorSpec extends ObjectBehavior
{
    const PROVIDER_NAME= 'some_provider_name';
    const PROVIDER_API_PATH = '/api/test';

    public function let(MatcherInterface $matcher): void
    {
        $this->beConstructedWith($matcher);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(InteractionCompositor::class);
    }

    public function it_creates_basic_consumer_request(): void
    {
        $dto = new InteractionRequestDTO(
            self::PROVIDER_NAME,
            '',
            self::PROVIDER_API_PATH
        );

        $this->createRequestFromDTO($dto)
            ->shouldBeAnInstanceOf(ConsumerRequest::class);
    }

    public function it_creates_basic_provider_response(): void
    {
        $dto = new InteractionResponseDTO(200);

        $this->createResponseFromDTO($dto)
            ->shouldBeAnInstanceOf(ProviderResponse::class);
    }

    public function it_creates_tablenode_based_provider_response(): void
    {
        $response = new TableNode(
            [
                ['parameter', 'value'],
                ['test1', 'test1value'],
                ['test2', '1'],
            ]
        );

        $dto = new InteractionResponseDTO(200, $response->getHash());

        $this->createResponseFromDTO($dto)
            ->shouldBeAnInstanceOf(ProviderResponse::class);
    }

    public function it_creates_tablenode_based_provider_response_with_eachLike(): void
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

        $this->createResponseFromDTO($dto)
            ->shouldBeAnInstanceOf(ProviderResponse::class);
    }

    public function it_creates_tablenode_based_provider_response_with_custom_matcher(): void
    {
        $response = new TableNode(
            [
                ['parameter', 'value', 'match'],
                ['test1', 'test1value', 'like'],
                ['test2', 'true', 'boolean'],
            ]
        );

        $dto = new InteractionResponseDTO(200, $response->getHash());

        $this->createResponseFromDTO($dto)
            ->shouldBeAnInstanceOf(ProviderResponse::class);
    }

    public function it_creates_stdClass_based_provider_response(): void
    {
        $response = new stdClass();
        $response->test1 = 'test1Value';
        $response->test2 = true;

        $dto = new InteractionResponseDTO(200, $response);

        $this->createResponseFromDTO($dto)
            ->shouldBeAnInstanceOf(ProviderResponse::class);
    }
}
