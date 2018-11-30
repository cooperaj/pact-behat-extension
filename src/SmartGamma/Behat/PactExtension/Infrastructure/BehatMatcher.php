<?php

namespace SmartGamma\Behat\PactExtension\Infrastructure;

use PhpPact\Consumer\Matcher\Matcher;

class BehatMatcher implements MatcherInterface
{
    /**
     * @var Matcher
     */
    private $pactMatcher;

    public function __construct(Matcher $matcher)
    {
        $this->pactMatcher = $matcher;
    }

    /**
     * @param $value
     *
     * @return array
     * @throws \Exception
     */
    public function like($value)
    {
        return $this->pactMatcher->like($this->normolizeValue($value));
    }

    /**
     * @param string $string
     *
     * @return bool|float|int|null|string
     */
    public function normolizeValue(string $string)
    {
        $string = mb_strtolower(trim($string));

        if (empty($string)) {
            return '';
        }

        if ('null' === $string) {
            return null;
        }

        if (!preg_match('/[^0-9.]+/', $string)) {
            if (preg_match('/[.]+/', $string)) {
                return (float)$string;
            }

            return (int)$string;
        }

        if ('true' === $string) {
            return true;
        }
        if ('false' === $string) {
            return false;
        }

        return (string)$string;
    }
}