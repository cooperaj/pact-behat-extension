<?php

namespace SmartGamma\Behat\PactExtension\Context\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use PhpPact\Consumer\Matcher\Matcher;
use SmartGamma\Behat\PactExtension\Context\PactContextInterface;
use SmartGamma\Behat\PactExtension\Infrastructure\Pact;

class PactInitializer implements ContextInitializer
{
    /**
     * @var Matcher
     */
    private $matcher;

    /**
     * @var Pact
     */
    private $pact;

    /**
     * PactInitializer constructor.
     *
     * @param Matcher $matcher
     * @param Pact    $pact
     */
    public function __construct(
        Matcher $matcher,
        Pact $pact
    )
    {
        $this->matcher = $matcher;
        $this->pact = $pact;
    }

    public function supports($context)
    {
        return $context instanceof Context;
    }

    public function initializeContext(Context $context)
    {
        if (false === $this->supports($context)) {

            return;
        }

        $context->initialize($this->pact, $this->matcher);
    }
}
