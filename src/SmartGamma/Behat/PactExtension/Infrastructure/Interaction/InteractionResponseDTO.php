<?php

declare(strict_types=1);

namespace SmartGamma\Behat\PactExtension\Infrastructure\Interaction;

use stdClass;

class InteractionResponseDTO
{
    private int $status;

    /** @var mixed[]|stdClass */
    private array|stdClass $rawParameters;

    /** @var mixed[] */
    private array $matchingObjectStructures = [];

    /**
     * InteractionResponseDTO constructor.
     *
     * @param int              $status
     * @param mixed[]|stdClass $rawParameters
     * @param mixed[]          $matchingObjectStructures
     */
    public function __construct(int $status, array|stdClass $rawParameters = [], array $matchingObjectStructures = [])
    {
        $this->status = $status;
        $this->rawParameters = $rawParameters;
        $this->matchingObjectStructures = $matchingObjectStructures;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return mixed[]|stdClass
     */
    public function getRawParameters(): array|stdClass
    {
        return $this->rawParameters;
    }

    /**
     * @param string $objectName
     * @return mixed[]
     */
    public function getMatchingObjectStructure(string $objectName): array
    {
        return $this->matchingObjectStructures[$objectName];
    }
}
