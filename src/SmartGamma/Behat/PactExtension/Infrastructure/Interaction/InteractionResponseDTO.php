<?php

namespace SmartGamma\Behat\PactExtension\Infrastructure\Interaction;

class InteractionResponseDTO
{
    /**
     * @var int
     */
    private $status;

    /**
     * @var array
     */
    private $rawParameters;

    /**
     * @var array
     */
    private $matchingObjectStructures = [];

    /**
     * InteractionResponseDTO constructor.
     *
     * @param int   $status
     * @param array $rawParameters
     * @param array $matchingObjectStructures
     */
    public function __construct(int $status, array $rawParameters = [], array $matchingObjectStructures = [])
    {
        $this->status = $status;
        $this->rawParameters = $rawParameters;
        $this->matchingObjectStructures = $matchingObjectStructures;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return array
     */
    public function getRawParameters(): array
    {
        return $this->rawParameters;
    }

    /**
     * @param string $objectName
     *
     * @return mixed
     */
    public function getMatchingObjectStructure(string $objectName)
    {
        return $this->matchingObjectStructures[$objectName];
    }
}
