<?php

namespace SmartGamma\Behat\PactExtension\Context;

use SmartGamma\Behat\PactExtension\Exception\NoAuthTypeSupported;

class Authenticator
{
    /**
     * @param string $authType
     * @param string $credentials
     *
     * @return array
     *
     * @throws NoAuthTypeSupported
     */
    public function authorizeConsumerRequestToProvider(string $authType, string $credentials): array
    {
        switch ($authType) {
            case 'http':
                $headers = ['Authorization' => 'Basic ' . base64_encode($credentials)];
                break;
            default:
                throw new NoAuthTypeSupported('No authorization type:' . $authType . ' is supported');
        }

        return $headers;
    }
}