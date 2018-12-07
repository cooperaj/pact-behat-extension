<?php

namespace spec\SmartGamma\Behat\PactExtension\Infrastructure;

use PhpPact\Consumer\Model\ConsumerRequest;
use PhpPact\Consumer\Model\ProviderResponse;
use SmartGamma\Behat\PactExtension\Exception\NoAuthTypeSupported;
use SmartGamma\Behat\PactExtension\Infrastructure\InteractionCompositor;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use SmartGamma\Behat\PactExtension\Infrastructure\MatcherInterface;

class InteractionCompositorSpec extends ObjectBehavior
{
    const PROVIDER_NAME= 'some_provider_name';
    const PROVIDER_API_PATH = '/api/test';

    public function let(MatcherInterface $matcher)
    {
        $this->beConstructedWith($matcher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(InteractionCompositor::class);
    }

    public function it_authorize_consumer_request_to_provider()
    {
        $this->authorizeConsumerRequestToProvider('http','user:pass', self::PROVIDER_NAME);
    }

    public function it_throws_exception_for_unsupported_auth_type()
    {
        $this->shouldThrow(new NoAuthTypeSupported('No authorization type:other is supported'))->during('authorizeConsumerRequestToProvider', ['other','user:pass', 'some_provider_name']);

    }

    public function it_create_consumer_request()
    {
        $this->createRequest(self::PROVIDER_NAME, 'GET', self::PROVIDER_API_PATH)->shouldBeAnInstanceOf(ConsumerRequest::class);
    }

    public function it_create_provider_response()
    {
        $this->createResponse(200)->shouldBeAnInstanceOf(ProviderResponse::class);
    }
}
