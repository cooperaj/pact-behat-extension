<?php

namespace spec\SmartGamma\Behat\PactExtension\Context\Initializer;

use SmartGamma\Behat\PactExtension\Context\Initializer\PactInitializer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use SmartGamma\Behat\PactExtension\Context\PactContextInterface;
use SmartGamma\Behat\PactExtension\Infrastructure\Pact;
use PhpPact\Consumer\Matcher\Matcher;

class PactInitializerSpec extends ObjectBehavior
{
    function let(Pact $pact,Matcher $matcher)
    {
        $this->beConstructedWith($matcher,$pact);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PactInitializer::class);
    }

    function it_should_supports_contexts(PactContextInterface $context1)
    {
        $this->supports($context1)->shouldReturn(true);
    }

    function it_should_not_supports_non_contexts(\stdClass $constext2)
    {
        $this->supports($constext2)->shouldReturn(false);
    }
}
