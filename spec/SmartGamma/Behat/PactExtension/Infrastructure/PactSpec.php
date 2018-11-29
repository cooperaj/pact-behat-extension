<?php

namespace spec\SmartGamma\Behat\PactExtension\Infrastructure;

use SmartGamma\Behat\PactExtension\Infrastructure\Pact;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PactSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([],[]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Pact::class);
    }
}
