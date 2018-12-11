<?php

namespace spec\SmartGamma\Behat\PactExtension\Infrastructure\Factory;

use SmartGamma\Behat\PactExtension\Infrastructure\Factory\InteractionBuilderFactory;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class InteractionBuilderFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InteractionBuilderFactory::class);
    }
}
