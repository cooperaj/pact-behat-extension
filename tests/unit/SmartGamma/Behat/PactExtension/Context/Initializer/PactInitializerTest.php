<?php

declare(strict_types=1);

namespace Tests\SmartGamma\Behat\PactExtension\Context\Initializer;

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SmartGamma\Behat\PactExtension\Context\Authenticator;
use SmartGamma\Behat\PactExtension\Context\Initializer\PactInitializer;
use SmartGamma\Behat\PactExtension\Context\PactContextInterface;
use SmartGamma\Behat\PactExtension\Infrastructure\Pact;
use SmartGamma\Behat\PactExtension\Infrastructure\ProviderState\ProviderState;

#[CoversClass(PactInitializer::class)]
final class PactInitializerTest extends TestCase
{
    private PactInitializer $pactInitializer;

    protected function setUp(): void
    {
        $pactMock              = $this->createMock(Pact::class);
        $providerStateMock     = $this->createMock(ProviderState::class);
        $authenticatorMock     = $this->createMock(Authenticator::class);
        $this->pactInitializer = new PactInitializer(
            $pactMock,
            $providerStateMock,
            $authenticatorMock,
        );
    }

    #[Test]
    public function supportsPactContexts(): void
    {
        $contextMock = $this->createMock(PactContextInterface::class);

        $this->assertTrue($this->pactInitializer->initializeContext($contextMock));
    }

    #[Test]
    public function notSupportsOtherBehatContexts(): void
    {
        $contextMock = $this->createMock(Context::class);

        $this->assertFalse($this->pactInitializer->initializeContext($contextMock));
    }
}
