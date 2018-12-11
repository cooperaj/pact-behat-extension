<?php

namespace SmartGamma\Behat\PactExtension\Context\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use SmartGamma\Behat\PactExtension\Context\PactContextInterface;
use SmartGamma\Behat\PactExtension\Infrastructure\Pact;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\InteractionCompositor;

class PactInitializer implements ContextInitializer
{
    /**
     * @var Pact
     */
    private $pact;

    /**
     * @var InteractionCompositor
     */
    private $compositor;

    /**
     * PactInitializer constructor.
     *
     * @param Pact    $pact
     */
    public function __construct(
        Pact $pact
    )
    {
        $this->pact       = $pact;
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
     */
    public function initializeContext(Context $context)
    {
        if (false === $this->supports($context)) {

            return;
        }

        $context->initialize($this->pact);
    }
}