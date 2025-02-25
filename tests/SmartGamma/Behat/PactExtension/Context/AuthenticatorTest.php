<?php

declare(strict_types=1);

namespace Tests\SmartGamma\Behat\PactExtension\Context;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SmartGamma\Behat\PactExtension\Context\Authenticator;
use SmartGamma\Behat\PactExtension\Exception\NoAuthTypeSupported;

#[CoversClass(Authenticator::class)]
final class AuthenticatorTest extends TestCase
{
    private Authenticator $authenticator;

    protected function setUp(): void
    {
        $this->authenticator = new Authenticator();
    }

    #[Test]
    public function createsHttpAuthorizationHeaders(): void
    {
        $result = $this->authenticator->authorizeConsumerRequestToProvider('http', 'username:password');

        $this->assertEquals(['Authorization' => 'Basic dXNlcm5hbWU6cGFzc3dvcmQ='], $result);
    }

    #[Test]
    public function throwsAnExceptionWhenUnknownAuthorizationTypePassed(): void
    {
        $this->expectException(NoAuthTypeSupported::class);
        $this->authenticator->authorizeConsumerRequestToProvider('digest', 'username:password');
    }
}