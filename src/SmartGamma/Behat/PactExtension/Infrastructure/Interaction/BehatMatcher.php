<?php

namespace SmartGamma\Behat\PactExtension\Infrastructure\Interaction;

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
     * @param $value
     *
     * @return mixed
     */
    public function exact($value)
    {
        return $this->normolizeValue($value);
    }

    /**
     * @param string $value
     *
     * @return array
     * @throws \Exception
     */
    public function dateTimeISO8601(string $value)
    {
        return $this->pactMatcher->dateTimeISO8601($value);
    }

    /**
     * @param string $value
     *
     * @return array
     * @throws \Exception
     */
    public function boolean(string $value)
    {
        return $this->pactMatcher->boolean();
    }

    /**
     * @param string $value
     *
     * @return array
     * @throws \Exception
     */
    public function integer(string $value)
    {
        return $this->pactMatcher->integer((int)$value);
    }

    /**
     * @param string $value
     *
     * @return array
     * @throws \Exception
     */
    public function uuid(string $value)
    {
        return $this->pactMatcher->uuid($value);
    }

    /**
     * @param array $object
     *
     * @return array
     */
    public function eachLike(array $object)
    {
        return $this->pactMatcher->eachLike($object);
    }

    /**
     * @param string $string
     *
     * @return bool | float | int | null | string
     */
    private function normolizeValue(string $string)
    {
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
