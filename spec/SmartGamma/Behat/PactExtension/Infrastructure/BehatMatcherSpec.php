<?php

namespace spec\SmartGamma\Behat\PactExtension\Infrastructure;

use SmartGamma\Behat\PactExtension\Infrastructure\BehatMatcher;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PhpPact\Consumer\Matcher\Matcher;

class BehatMatcherSpec extends ObjectBehavior
{
    public function let(Matcher $matcher)
    {
        $this->beConstructedWith($matcher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(BehatMatcher::class);
    }
}
