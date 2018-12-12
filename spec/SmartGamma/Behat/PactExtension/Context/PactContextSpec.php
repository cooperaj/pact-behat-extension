<?php

namespace spec\SmartGamma\Behat\PactExtension\Context;

use SmartGamma\Behat\PactExtension\Context\Authenticator;
use SmartGamma\Behat\PactExtension\Context\PactContext;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use SmartGamma\Behat\PactExtension\Infrastructure\Pact;
use SmartGamma\Behat\PactExtension\Infrastructure\ProviderState\ProviderState;

class PactContextSpec extends ObjectBehavior
{
    public function let(Pact $pact, ProviderState $providerState, Authenticator $authenticator)
    {
        $this->initialize($pact, $providerState, $authenticator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PactContext::class);
    }
}
