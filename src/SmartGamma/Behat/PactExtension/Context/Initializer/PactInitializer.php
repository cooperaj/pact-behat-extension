<?php

namespace SmartGamma\Behat\PactExtension\Context\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use SmartGamma\Behat\PactExtension\Context\PactContextInterface;
use SmartGamma\Behat\PactExtension\Infrastructure\MatcherInterface;
use SmartGamma\Behat\PactExtension\Infrastructure\Pact;
use SmartGamma\Behat\PactExtension\Infrastructure\InteractionCompositor;

class PactInitializer implements ContextInitializer
{
    /**
     * @var MatcherInterface
     */
    private $matcher;

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
     * @param Matcher $matcher
     * @param Pact    $pact
     */
    public function __construct(
        MatcherInterface $matcher,
        Pact $pact,
        InteractionCompositor $compositor
    )
    {
        $this->matcher = $matcher;
        $this->pact = $pact;
        $this->compositor = $compositor;
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

        $context->initialize($this->pact, $this->matcher, $this->compositor);
    }
}
