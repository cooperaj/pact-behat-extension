<?php

declare(strict_types=1);

namespace Tests\SmartGamma\Behat\PactExtension\Infrastructure\Factory;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use PhpPact\Http\GuzzleClient;
use PhpPact\Standalone\MockService\MockServerConfigInterface;
use PhpPact\Standalone\MockService\Service\MockServerHttpService;
use SmartGamma\Behat\PactExtension\Infrastructure\Factory\MockServerHttpServiceFactory;

#[CoversClass(MockServerHttpServiceFactory::class)]
final class MockServerHttpServiceFactoryTest extends TestCase
{
    private MockObject|GuzzleClient $clientMock;
    private MockServerHttpServiceFactory $mockServerHttpServiceFactory;

    protected function setUp(): void
    {
        $this->clientMock = $this->createMock(GuzzleClient::class);
        $this->mockServerHttpServiceFactory = new MockServerHttpServiceFactory($this->clientMock);
    }

    #[Test]
    function createsHttpService(): void
    {
        $configMock = $this->createMock(MockServerConfigInterface::class);

        $this->assertInstanceOf(
            MockServerHttpService::class,
            $this->mockServerHttpServiceFactory->create($configMock)
        );
    }
}
