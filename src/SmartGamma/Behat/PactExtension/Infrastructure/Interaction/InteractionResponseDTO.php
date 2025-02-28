<?php

declare(strict_types=1);

namespace SmartGamma\Behat\PactExtension\Infrastructure\Interaction;

use stdClass;

class InteractionResponseDTO
{
    /**
     * @param int                                     $status
     * @param array<string, scalar|string[]>|stdClass $rawParameters
     * @param array<string, string[]>                 $matchingObjectStructures
     */
    public function __construct(
        private readonly int $status,
        private readonly array|stdClass $rawParameters = [],
        private readonly array $matchingObjectStructures = [],
    ) {
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return array<string, scalar|string[]>|stdClass
     */
    public function getRawParameters(): array|stdClass
    {
        return $this->rawParameters;
    }

    /**
     * @param string $objectName
     *
     * @return string[]
     */
    public function getMatchingObjectStructure(string $objectName): array
    {
        return $this->matchingObjectStructures[$objectName];
    }
}
