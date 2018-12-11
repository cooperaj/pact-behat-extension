<?php

namespace spec\SmartGamma\Behat\PactExtension\Infrastructure\Interaction;

use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\ProviderState;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProviderInjectorStateSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProviderState::class);
    }
}
