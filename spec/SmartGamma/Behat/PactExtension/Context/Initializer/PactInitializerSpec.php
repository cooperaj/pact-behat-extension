<?php

namespace spec\SmartGamma\Behat\PactExtension\Context\Initializer;

use Behat\Behat\Context\Context;
use SmartGamma\Behat\PactExtension\Context\Authenticator;
use SmartGamma\Behat\PactExtension\Context\Initializer\PactInitializer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use SmartGamma\Behat\PactExtension\Context\PactContextInterface;
use SmartGamma\Behat\PactExtension\Infrastructure\Pact;
use SmartGamma\Behat\PactExtension\Infrastructure\ProviderState\ProviderState;

class PactInitializerSpec extends ObjectBehavior
{
    function let(Pact $pact, ProviderState $providerState, Authenticator $authenticator)
    {
        $this->beConstructedWith($pact, $providerState, $authenticator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PactInitializer::class);
    }

    function it_should_supports_pact_contexts(PactContextInterface $context)
    {
        $this->initializeContext($context)->shouldReturn(true);
    }

    function it_should_not_supports_other_behat_contexts(Context $constext)
    {
        $this->initializeContext($constext)->shouldReturn(false);
    }
}