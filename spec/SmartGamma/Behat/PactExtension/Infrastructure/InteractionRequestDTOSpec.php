<?php

namespace spec\SmartGamma\Behat\PactExtension\Infrastructure;

use SmartGamma\Behat\PactExtension\Infrastructure\InteractionRequestDTO;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class InteractionRequestDTOSpec extends ObjectBehavior
{
    const PROVIDER_NAME = 'some provider';
    const STEP_NAME = 'step name';

    public function let()
    {
        $this->beConstructedWith(self::PROVIDER_NAME, self::STEP_NAME, '/');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(InteractionRequestDTO::class);
    }
}
