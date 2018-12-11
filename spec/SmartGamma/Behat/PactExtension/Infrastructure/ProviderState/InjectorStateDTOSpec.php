<?php

namespace spec\SmartGamma\Behat\PactExtension\Infrastructure\Interaction;

use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\InjectorStateDTO;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class InjectorStateDTOSpec extends ObjectBehavior
{
    const PROVIDER_NAME = 'provider name';

    public function let()
    {
        $this->beConstructedWith(self::PROVIDER_NAME);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(InjectorStateDTO::class);
    }

    function it_returns_provider_name()
    {
        $this->getProviderName()->shouldBe(self::PROVIDER_NAME);
    }
}
