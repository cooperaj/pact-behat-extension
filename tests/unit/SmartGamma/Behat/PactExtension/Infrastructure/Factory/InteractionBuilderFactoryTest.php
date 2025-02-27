<?php

declare(strict_types=1);

namespace Tests\SmartGamma\Behat\PactExtension\Infrastructure\Factory;

use PhpPact\Consumer\InteractionBuilder;
use PhpPact\Standalone\MockService\MockServerConfigInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SmartGamma\Behat\PactExtension\Infrastructure\Factory\InteractionBuilderFactory;

#[CoversClass(InteractionBuilderFactory::class)]
final class InteractionBuilderFactoryTest extends TestCase
{
    #[Test]
    public function createsInteractionBuilder(): void
    {
        $mockConfig = $this->createMock(MockServerConfigInterface::class);

        $sut = new InteractionBuilderFactory();

        $builder = $sut->create($mockConfig);
        $this->assertInstanceOf(InteractionBuilder::class, $builder);
    }
}
