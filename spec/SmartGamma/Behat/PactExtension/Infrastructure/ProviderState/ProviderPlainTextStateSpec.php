<?php

namespace spec\SmartGamma\Behat\PactExtension\Infrastructure\Interaction;

use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\PlainTextStateDTO;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProviderPlainTextStateSpec extends ObjectBehavior
{
    const TEXT_STATE = 'text state';

    public function let()
    {
        $this->beConstructedWith(self::TEXT_STATE);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PlainTextStateDTO::class);
    }
    
    public function it_gets_state_description()
    {
        $this->getStateDescription()->shouldBe(self::TEXT_STATE);
    }
}
