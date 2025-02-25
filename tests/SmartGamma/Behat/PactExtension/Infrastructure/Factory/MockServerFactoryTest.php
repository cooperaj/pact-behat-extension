<?php

declare(strict_types=1);

namespace SmartGamma\Behat\PactExtension\Infrastructure\Factory;

use PhpPact\Standalone\MockService\MockServer;
use PhpPact\Standalone\MockService\MockServerConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MockServerFactory::class)]
final class MockServerFactoryTest extends TestCase
{
    #[Test]
    public function createsMockServer(): void
    {
        $mockConfig = $this->createMock(MockServerConfig::class);

        $sut = new MockServerFactory();

        $builder = $sut->create($mockConfig);
        $this->assertInstanceOf(MockServer::class, $builder);
    }
}
