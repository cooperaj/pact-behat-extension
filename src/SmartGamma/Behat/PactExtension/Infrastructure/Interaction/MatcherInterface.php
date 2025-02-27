<?php

declare(strict_types=1);

namespace SmartGamma\Behat\PactExtension\Infrastructure\Interaction;

use Exception;
use PhpPact\Consumer\Matcher\Model\Matcher\JsonFormattableInterface;
use PhpPact\Consumer\Matcher\Model\MatcherInterface as PactPHPMatcherInterface;

interface MatcherInterface
{
    const EXACT_TYPE = 'exact';
    const EACH_LIKE_TYPE = 'eachLike';

    /**
     * @template T
     * @param T $value
     *
     * @return T
     */
    public function exact(mixed $value): mixed;

    /**
     * @template T of bool|float|int|null|string
     * @param T $value
     *
     * @return PactPHPMatcherInterface
     * @throws Exception
     */
    public function like(mixed $value): PactPHPMatcherInterface;

    /**
     * @param string $value
     *
     * @return PactPHPMatcherInterface
     * @throws Exception
     */
    public function dateTimeISO8601(string $value): PactPHPMatcherInterface;

    /**
     * @template T of bool|float|int|null|string
     * @param T $value
     *
     * @return PactPHPMatcherInterface
     * @throws Exception
     */
    public function boolean(mixed $value): PactPHPMatcherInterface;

    /**
     * @param string|int $value
     *
     * @return PactPHPMatcherInterface
     * @throws Exception
     */
    public function integer(string|int $value): PactPHPMatcherInterface;

    /**
     * @param string $value
     *
     * @return PactPHPMatcherInterface
     * @throws Exception
     */
    public function uuid(string $value): PactPHPMatcherInterface;

    /**
     * @template T
     * @param T $object
     *
     * @return PactPHPMatcherInterface
     * @throws Exception
     */
    public function eachLike(mixed $object): PactPHPMatcherInterface;
}
