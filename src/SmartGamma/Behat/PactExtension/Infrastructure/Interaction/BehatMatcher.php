<?php

declare(strict_types=1);

namespace SmartGamma\Behat\PactExtension\Infrastructure\Interaction;

use PhpPact\Consumer\Matcher\Matcher;

class BehatMatcher implements MatcherInterface
{
    private Matcher $pactMatcher;

    public function __construct(Matcher $matcher)
    {
        $this->pactMatcher = $matcher;
    }

    public function like(mixed $value): array
    {
        return $this->pactMatcher->like($this->normaliseValue($value));
    }

    public function exact(mixed $value): mixed
    {
        return $value;
    }

    public function dateTimeISO8601(string $value): array
    {
        return $this->pactMatcher->dateTimeISO8601($value);
    }

    public function boolean(mixed $value): array
    {
        return $this->pactMatcher->boolean();
    }

    public function integer(string|int $value): array
    {
        return $this->pactMatcher->integer((int) $value);
    }

    public function uuid(string $value): array
    {
        return $this->pactMatcher->uuid($value);
    }

    public function eachLike(mixed $object): array
    {
        return $this->pactMatcher->eachLike($object);
    }

    /**
     * @template T of bool|float|int|null|string
     * @param T $value
     *
     * @return T
     */
    private function normaliseValue(mixed $value): mixed
    {
        if (empty($value)) {
            return '';
        }

        if ('null' === $value) {
            return null;
        }

        if (!preg_match('/[^0-9.]+/', $value)) {
            if (preg_match('/[.]+/', $value)) {
                return (float)$value;
            }

            return (int)$value;
        }

        if ('true' === $value) {
            return true;
        }

        if ('false' === $value) {
            return false;
        }

        return (string)$value;
    }
}
