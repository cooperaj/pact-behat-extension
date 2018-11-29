<?php

namespace spec\SmartGamma\Behat\PactExtension\Context;

use SmartGamma\Behat\PactExtension\Context\PactContext;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PactContextSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PactContext::class);
    }
}
