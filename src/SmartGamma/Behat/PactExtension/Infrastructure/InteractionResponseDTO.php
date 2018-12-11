<?php

namespace SmartGamma\Behat\PactExtension\Infrastructure;

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
     * InteractionResponseDTO constructor.
     *
     * @param int   $status
     * @param array $rawParameters
     */
    public function __construct(int $status, array $rawParameters = [])
    {
        $this->status = $status;
        $this->rawParameters = $rawParameters;
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
}
