<?php

declare(strict_types=1);

namespace SmartGamma\Behat\PactExtension\Infrastructure\Interaction;

use stdClass;

class InteractionResponseDTO
{
    private int $status;

    /** @var array|stdClass */
    private $rawParameters;

    private array $matchingObjectStructures = [];

    /**
     * InteractionResponseDTO constructor.
     *
     * @param int            $status
     * @param array|stdClass $rawParameters
     * @param array          $matchingObjectStructures
     */
    public function __construct(int $status, $rawParameters = [], array $matchingObjectStructures = [])
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
     * @return array|stdClass
     */
    public function getRawParameters()
    {
        return $this->rawParameters;
    }

    /**
     * @param string $objectName
     * @return array
     */
    public function getMatchingObjectStructure(string $objectName): array
    {
        return $this->matchingObjectStructures[$objectName];
    }
}
