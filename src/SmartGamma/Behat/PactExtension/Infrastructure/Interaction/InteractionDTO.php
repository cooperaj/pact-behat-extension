<?php

declare(strict_types=1);

namespace SmartGamma\Behat\PactExtension\Infrastructure\Interaction;

class InteractionDTO
{
    public function __construct(
        private readonly InteractionRequestDTO $requestDTO,
        private readonly InteractionResponseDTO $responseDTO,
        private readonly string $providerState,
    ) {
    }

    public function getRequestDTO(): InteractionRequestDTO
    {
        return $this->requestDTO;
    }

    public function getResponseDTO(): InteractionResponseDTO
    {
        return $this->responseDTO;
    }

    public function getProviderState(): string
    {
        return $this->providerState;
    }
}
