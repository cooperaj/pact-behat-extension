<?php

declare(strict_types=1);

namespace SmartGamma\Behat\PactExtension\Infrastructure\Interaction;

use Exception;
use PhpPact\Consumer\Matcher\Model\Matcher\JsonFormattableInterface;
use PhpPact\Consumer\Matcher\Model\MatcherInterface as PactPHPMatcherInterface;

interface MatcherInterface
{
    const EXACT_TYPE     = 'exact';
    const EACH_LIKE_TYPE = 'eachLike';

    /**
     * @param T $value
     *
     * @return T
     *
     * @template T
     */
    public function exact(mixed $value): mixed;

    /**
     * @param T $value
     *
     * @return PactPHPMatcherInterface
     * @throws Exception
     *
     * @template T of bool|float|int|null|string
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
     * @param T $value
     *
     * @return PactPHPMatcherInterface
     * @throws Exception
     *
     * @template T of bool|float|int|null|string
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
     * @param T $object
     *
     * @return PactPHPMatcherInterface
     * @throws Exception
     *
     * @template T
     */
    public function eachLike(mixed $object): PactPHPMatcherInterface;
}
