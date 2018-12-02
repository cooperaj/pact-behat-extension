<?php

namespace SmartGamma\Behat\PactExtension\Context;

use Behat\Behat\Context\Context;
use SmartGamma\Behat\PactExtension\Infrastructure\InteractionCompositor;
use SmartGamma\Behat\PactExtension\Infrastructure\MatcherInterface;
use SmartGamma\Behat\PactExtension\Infrastructure\Pact;

interface PactContextInterface extends Context
{
    public function initialize(Pact $pact, InteractionCompositor $compositor);
}
