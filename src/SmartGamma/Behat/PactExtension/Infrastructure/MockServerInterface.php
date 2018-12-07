<?php

namespace SmartGamma\Behat\PactExtension\Infrastructure;

interface MockServerInterface
{
    public function start(): int;
}
