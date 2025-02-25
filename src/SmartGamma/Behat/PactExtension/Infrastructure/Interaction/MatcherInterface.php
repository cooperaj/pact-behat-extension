<?php

declare(strict_types=1);

namespace SmartGamma\Behat\PactExtension\Infrastructure\Interaction;

use Exception;

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
     * @return array{
     *     contents: T,
     *     json_class: string,
     *     ...
     * }
     * @throws Exception
     */
    public function like(mixed $value): array;

    /**
     * @param string $value
     *
     * @return array{
     *     data: mixed[],
     *     json_class: string,
     *     ...
     * }
     * @throws Exception
     */
    public function dateTimeISO8601(string $value): array;

    /**
     * @param mixed $value
     *
     * @return array{
     *     contents: bool,
     *     json_class: string,
     *     ...
     * }
     * @throws Exception
     */
    public function boolean(mixed $value): array;

    /**
     * @param string|int $value
     *
     * @return array{
     *     contents: int,
     *     json_class: string,
     *     ...
     * }
     * @throws Exception
     */
    public function integer(string|int $value): array;

    /**
     * @param string $value
     *
     * @return array{
     *     data: mixed[],
     *     json_class: string,
     *     ...
     * }
     * @throws Exception
     */
    public function uuid(string $value): array;

    /**
     * @template T
     * @param T $object
     *
     * @return array{
     *     contents: T,
     *     json_class: string,
     *     ...
     * }
     * @throws Exception
     */
    public function eachLike(mixed $object): array;
}
