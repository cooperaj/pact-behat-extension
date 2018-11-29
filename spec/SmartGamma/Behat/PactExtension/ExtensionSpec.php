<?php

namespace spec\SmartGamma\Behat\PactExtension;

use SmartGamma\Behat\PactExtension\Extension;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ExtensionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Extension::class);
    }
}
