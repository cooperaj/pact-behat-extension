<?php

declare(strict_types=1);

namespace spec\SmartGamma\Behat\PactExtension\Context;

use PhpSpec\ObjectBehavior;
use SmartGamma\Behat\PactExtension\Context\Authenticator;
use SmartGamma\Behat\PactExtension\Exception\NoAuthTypeSupported;

class AuthenticatorSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(Authenticator::class);
    }

    public function it_creates_http_authorization_headers(): void
    {
        $this->authorizeConsumerRequestToProvider('http', 'username:password')
            ->shouldHaveKeyWithValue('Authorization', 'Basic dXNlcm5hbWU6cGFzc3dvcmQ=');
    }

    public function it_throws_an_exception_when_unknown_authorization_type_passed(): void
    {
        $this->shouldThrow(NoAuthTypeSupported::class)
            ->duringAuthorizeConsumerRequestToProvider('digest', 'username:password');
    }
}