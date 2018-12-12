<?php

namespace SmartGamma\Behat\PactExtension\Infrastructure\ProviderState;

class ProviderState implements ProviderStateInterface
{
    /**
     * @var array
     */
    private $injectors = [];

    /**
     * @var array
     */
    private $plainTextState = [];

    /**
     * @var string
     */
    private $defaultPlainTextState;

    /**
     * @param string $providerName
     *
     * @return string
     */
    public function getStateDescription(string $providerName): string
    {
        if (isset($this->injectors[$providerName])) {
            /** @var InjectorStateDTO $injector */
            $injector = $this->injectors[$providerName][0];
            $given    = 'Create '
                . $injector->getEntityName()
                . $injector->getEntityDescription()
                . ':'
                . \json_encode($injector->getParameters());

            return $given;
        }

        if (isset($this->plainTextState[$providerName])) {
            $given = $this->plainTextState [$providerName];

            return $given;
        }

        return $this->defaultPlainTextState;
    }

    public function addInjectorState(InjectorStateDTO $injectorStateDTO)
    {
        $this->injectors[$injectorStateDTO->getProviderName()][] = $injectorStateDTO;
    }

    public function setDefaultPlainTextState(string $text)
    {
        $this->defaultPlainTextState = $text;
    }

    public function setPlainTextState(PlainTextStateDTO $textStateDTO)
    {
        $this->plainTextState[$textStateDTO->getProviderName()] = $textStateDTO->getStateDescription();
    }

    /**
     * In order to clear states defined in the multiple scenarios in with context
     * Should be call in Context initializer
     */
    public function clearStates()
    {
        unset($this->injectors);
        unset($this->plainTextState);
        unset($this->defaultPlainTextState);
    }
}
