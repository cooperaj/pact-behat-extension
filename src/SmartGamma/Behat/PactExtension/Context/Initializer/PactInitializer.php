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
    private Pact $pact;

    private ProviderState $providerState;

    private Authenticator $authenticator;

    public function __construct(
        Pact $pact,
        ProviderState $providerState,
        Authenticator $authenticator
    )
    {
        $this->pact          = $pact;
        $this->providerState = $providerState;
        $this->authenticator = $authenticator;
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
