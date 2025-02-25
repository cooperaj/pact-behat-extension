<?php

declare(strict_types=1);

namespace SmartGamma\Behat\PactExtension\Infrastructure\Factory;

use GuzzleHttp\Psr7\Uri;
use PhpPact\Broker\Service\BrokerHttpClient;
use PhpPact\Http\GuzzleClient;
use RuntimeException;

class BrokerHttpClientFactory 
{
    private string $brokerUri;
    private string $authUser = '';
    private string $authPassword = '';

    /**
     * @param string[] $config
     */
    public function __construct(array $config)
    {
        $this->brokerUri = $config['PACT_BROKER_URI'] ?? '';
        if (isset($config['PACT_BROKER_HTTP_AUTH_USER'])
            && isset($config['PACT_BROKER_HTTP_AUTH_PASS'])) {
                $this->authUser = $config['PACT_BROKER_HTTP_AUTH_USER'];
                $this->authPassword = $config['PACT_BROKER_HTTP_AUTH_USER'];
        }
    }

    public function create(): BrokerHttpClient
    {
        if ($this->brokerUri === '') {
            throw new RuntimeException('Attempt to create Pact broker client without uri configured');
        }

        $clientConfig = [];
        if ($this->authUser !== '' && $this->authPassword !== '') {
            $clientConfig = [
                'auth' => [
                    $this->authUser,
                    $this->authPassword,
                ],
            ];
        }

        return new BrokerHttpClient(
            new GuzzleClient($clientConfig), 
            new Uri($this->brokerUri)
        );
    }
}