<?php

namespace spec\SmartGamma\Behat\PactExtension\Infrastructure;

use SmartGamma\Behat\PactExtension\Infrastructure\InteractionCompositor;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use SmartGamma\Behat\PactExtension\Infrastructure\MatcherInterface;

class InteractionCompositorSpec extends ObjectBehavior
{
    public function let(MatcherInterface $matcher)
    {
        $this->beConstructedWith($matcher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(InteractionCompositor::class);
    }
}
