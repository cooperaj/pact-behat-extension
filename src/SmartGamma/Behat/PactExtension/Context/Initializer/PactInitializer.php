<?php

declare(strict_types=1);

namespace SmartGamma\Behat\PactExtension\Context\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use SmartGamma\Behat\PactExtension\Context\Authenticator;
use SmartGamma\Behat\PactExtension\Context\PactContextInterface;
use SmartGamma\Behat\PactExtension\Infrastructure\Pact;
use SmartGamma\Behat\PactExtension\Infrastructure\ProviderState\ProviderState;

class PactInitializer implements ContextInitializer
{
    public function __construct(
        private Pact $pact,
        private ProviderState $providerState,
        private Authenticator $authenticator,
    ) {
    }

    public function supports(object $context): bool
    {
        return $context instanceof PactContextInterface;
    }

    public function initializeContext(Context $context): bool
    {
        if ($this->supports($context) === false) {
            return false;
        }

        /** @var PactContextInterface $context */
        $context->initialize($this->pact, $this->providerState, $this->authenticator);

        return true;
    }
}
