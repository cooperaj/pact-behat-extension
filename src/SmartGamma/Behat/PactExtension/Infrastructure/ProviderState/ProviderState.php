<?php

declare(strict_types=1);

namespace SmartGamma\Behat\PactExtension\Infrastructure\ProviderState;

class ProviderState implements ProviderStateInterface
{
    /** @var InjectorStateDTO[] */
    private array $injectors = [];

    /** @var string[] */
    private array $plainTextState = [];

    private string $defaultPlainTextState;

    public function getStateDescription(string $providerName): string
    {
        if (isset($this->injectors[$providerName])) {
            $injector = $this->injectors[$providerName][0];
            return 'Create '
                . $injector->getEntityName()
                . $injector->getEntityDescription()
                . ':'
                . \json_encode($injector->getParameters());
        }

        if (isset($this->plainTextState[$providerName])) {
            return $this->plainTextState[$providerName];
        }

        return $this->defaultPlainTextState;
    }

    public function addInjectorState(InjectorStateDTO $injectorStateDTO): void
    {
        $this->injectors[$injectorStateDTO->getProviderName()][] = $injectorStateDTO;
    }

    public function setDefaultPlainTextState(string $text): void
    {
        $this->defaultPlainTextState = $text;
    }

    public function setPlainTextState(PlainTextStateDTO $textStateDTO): void
    {
        $this->plainTextState[$textStateDTO->getProviderName()] = $textStateDTO->getStateDescription();
    }

    /**
     * In order to clear states defined in the multiple scenarios in with context
     */
    public function clearStates(): void
    {
        unset($this->injectors);
        unset($this->plainTextState);
        unset($this->defaultPlainTextState);
    }
}
