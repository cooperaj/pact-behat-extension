<?php

declare(strict_types=1);

namespace Tests\SmartGamma\Behat\PactExtension\Infrastructure\Factory;

use PhpPact\Broker\Service\BrokerHttpClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SmartGamma\Behat\PactExtension\Infrastructure\Factory\BrokerHttpClientFactory;

#[CoversClass(BrokerHttpClientFactory::class)]
final class BrokerHttpClientFactoryTest extends TestCase
{
    #[Test]
    public function needsUriConfiguration(): void
    {
        $sut = new BrokerHttpClientFactory([]);

        $this->expectException(RuntimeException::class);
        $result = $sut->create();
    }

    #[Test]
    public function createsBrokerHttpClient(): void
    {
        $sut = new BrokerHttpClientFactory(
            [
                'PACT_BROKER_URI' => 'http://test'
            ]
        );

        $result = $sut->create();

        $this->assertInstanceOf(BrokerHttpClient::class, $result);
    }

    #[Test]
    public function configuresAuthOnClient(): void
    {
        $sut = new BrokerHttpClientFactory(
            [
                'PACT_BROKER_URI' => 'http://test',
                'PACT_BROKER_HTTP_AUTH_USER' => 'test',
                'PACT_BROKER_HTTP_AUTH_PASS' => 'test',
            ]
        );

        $result = $sut->create();

        $this->assertInstanceOf(BrokerHttpClient::class, $result);
    }
}