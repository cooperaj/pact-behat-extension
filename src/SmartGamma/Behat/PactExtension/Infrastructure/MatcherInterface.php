<?php

namespace SmartGamma\Behat\PactExtension\Infrastructure;

use PhpPact\Consumer\Matcher\Matcher;

interface MatcherInterface
{
    public function like($value);
}