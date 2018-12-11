<?php

namespace SmartGamma\Behat\PactExtension\Infrastructure\Factory;

interface MockServerInterface
{
    public function start(): int;
}
