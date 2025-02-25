<?php

declare(strict_types=1);

namespace SmartGamma\Behat\PactExtension\Context;

use SmartGamma\Behat\PactExtension\Exception\NoAuthTypeSupported;

class Authenticator
{
    /**
     * @param string $authType
     * @param string $credentials
     *
     * @return array{
     *     Authorization: string
     * }
     *
     * @throws NoAuthTypeSupported
     */
    public function authorizeConsumerRequestToProvider(string $authType, string $credentials): array
    {
        return match ($authType) {
            'http' => ['Authorization' => 'Basic ' . base64_encode($credentials)],
            default => throw new NoAuthTypeSupported('No authorization type:' . $authType . ' is supported'),
        };
    }
}
