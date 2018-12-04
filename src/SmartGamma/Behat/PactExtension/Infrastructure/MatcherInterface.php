<?php

namespace SmartGamma\Behat\PactExtension\Infrastructure;

use PhpPact\Consumer\Matcher\Matcher;

interface MatcherInterface
{
    public function exact($value);
    public function like($value);
    public function dateTimeISO8601(string $value);
    public function boolean(string $value);
    public function integer(string $value);
    public function uuid(string $value);
}