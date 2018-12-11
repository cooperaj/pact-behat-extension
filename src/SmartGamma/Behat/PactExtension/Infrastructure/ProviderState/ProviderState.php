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
        /*
        if (isset($this->providerEntityData[$providerName]) && sizeof($this->providerEntityData[$providerName][$this->providerEntityName[$providerName]])) {

            $given = 'Create '
                . $this->providerEntityName[$providerName]
                . $this->providerEntityDescription[$providerName][$this->providerEntityName[$providerName]]
                . ':'
                . \json_encode($this->providerEntityData[$providerName][$this->providerEntityName[$providerName]]);

            return $given;
        }

        if (isset($this->providerTextState[$providerName])) {
            $given = $this->providerTextState[$providerName];

            return $given;
        }

        $given = self::$scenarioName;

        return $given;
        */

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
}
