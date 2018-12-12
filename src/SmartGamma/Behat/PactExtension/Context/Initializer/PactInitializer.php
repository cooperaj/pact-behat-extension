<?php

namespace SmartGamma\Behat\PactExtension\Context\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use SmartGamma\Behat\PactExtension\Context\Authenticator;
use SmartGamma\Behat\PactExtension\Context\PactContextInterface;
use SmartGamma\Behat\PactExtension\Infrastructure\Pact;
use SmartGamma\Behat\PactExtension\Infrastructure\ProviderState\ProviderState;

class PactInitializer implements ContextInitializer
{
    /**
     * @var Pact
     */
    private $pact;

    /**
     * @var ProviderState
     */
    private $providerState;

    /**
     * @var Authenticator
     */
    private $authenticator;

    /**
     * PactInitializer constructor.
     *
     * @param Pact          $pact
     * @param ProviderState $providerState
     * @param Authenticator $authenticator
     */
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

    /**
     * @param mixed $context
     *
     * @return bool
     */
    public function supports($context)
    {
        return $context instanceof PactContextInterface;
    }

    /**
     * @param Context $context
     *
     * @return bool
     */
    public function initializeContext(Context $context)
    {
        if (false === $this->supports($context)) {

            return false;
        }

        $context->initialize($this->pact, $this->providerState, $this->authenticator);

        return true;
    }
}