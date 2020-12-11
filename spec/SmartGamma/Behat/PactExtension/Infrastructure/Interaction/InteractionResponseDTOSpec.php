<?php

declare(strict_types=1);

namespace spec\SmartGamma\Behat\PactExtension\Infrastructure\Interaction;

use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\InteractionResponseDTO;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use stdClass;

class InteractionResponseDTOSpec extends ObjectBehavior
{
    function it_is_initializable_with_arrays(): void
    {
        $this->beConstructedWith(200, []);
        $this->shouldHaveType(InteractionResponseDTO::class);
    }

    function it_is_initializable_with_stdClass(): void
    {
        $this->beConstructedWith(200, new stdClass);
        $this->shouldHaveType(InteractionResponseDTO::class);
    }
}
