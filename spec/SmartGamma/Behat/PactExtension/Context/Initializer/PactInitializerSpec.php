<?php

namespace spec\SmartGamma\Behat\PactExtension\Context\Initializer;

use Behat\Behat\Context\Context;
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

    function it_should_supports_pact_contexts(PactContextInterface $context)
    {
        $this->supports($context)->shouldReturn(true);
    }

    function it_should_not_supports_other_behat_contexts(Context $constext)
    {
        $this->supports($constext)->shouldReturn(false);
    }

    function it_should_not_supports_non_contexts(\stdClass $constext)
    {
        $this->supports($constext)->shouldReturn(false);
    }
}
