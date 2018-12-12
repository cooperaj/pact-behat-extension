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
    const PROVIDER_NAME = 'provider_name';
    const PROVIDER_STATE_TEXT = 'phpspec provider state';

    public function let(Pact $pact, ProviderState $providerState, Authenticator $authenticator)
    {
        $providerState->getStateDescription(self::PROVIDER_NAME)->willReturn(self::PROVIDER_STATE_TEXT);

        $this->initialize($pact, $providerState, $authenticator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PactContext::class);
    }

    public function it_register_interaction()
    {
        $this->registerInteraction(self::PROVIDER_NAME, 'GET', '/', 200);
    }
}
