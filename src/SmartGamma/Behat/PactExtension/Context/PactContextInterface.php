<?php

namespace SmartGamma\Behat\PactExtension\Context;

use Behat\Behat\Context\Context;
use SmartGamma\Behat\PactExtension\Infrastructure\Pact;
use SmartGamma\Behat\PactExtension\Infrastructure\ProviderState\ProviderState;

interface PactContextInterface extends Context
{
    public function initialize(Pact $pact, ProviderState $providerState, Authenticator $authenticator);
}
