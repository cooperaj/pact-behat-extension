<?php

declare(strict_types=1);

namespace SmartGamma\Behat\PactExtension\Infrastructure\Factory;

interface MockServerInterface
{
    public function start(): int;
}
