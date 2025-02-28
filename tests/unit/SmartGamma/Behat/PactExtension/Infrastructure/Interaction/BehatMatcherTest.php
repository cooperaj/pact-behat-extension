<?php

declare(strict_types=1);

namespace Tests\SmartGamma\Behat\PactExtension\Infrastructure\Interaction;

use PhpPact\Consumer\Matcher\Matcher;
use PhpPact\Consumer\Matcher\Matchers\MinType;
use PhpPact\Consumer\Matcher\Matchers\Regex;
use PhpPact\Consumer\Matcher\Matchers\Type;
use PhpPact\Consumer\Matcher\Model\MatcherInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\BehatMatcher;

#[CoversClass(BehatMatcher::class)]
final class BehatMatcherTest extends TestCase
{
    private MockObject|Matcher $matcherMock;
    private BehatMatcher $behatMatcher;

    protected function setUp(): void
    {
        $this->matcherMock  = $this->createMock(Matcher::class);
        $this->behatMatcher = new BehatMatcher($this->matcherMock);
    }

    #[Test]
    public function matchesExact(): void
    {
        $this->assertSame('my value', $this->behatMatcher->exact('my value'));
    }

    #[Test]
    public function matchesLike(): void
    {
        $this->matcherMock
            ->expects($this->once())
            ->method('like')
            ->with($this->equalTo('my value'))
            ->willReturn($this->createMock(Type::class));

        $this->assertInstanceOf(
            MatcherInterface::class,
            $this->behatMatcher->like('my value'),
        );
    }

    #[Test]
    public function matchesInteger(): void
    {
        $this->matcherMock
            ->expects($this->once())
            ->method('integer')
            ->with($this->isType('int'))
            ->willReturn($this->createMock(Type::class));

        $this->assertInstanceOf(
            MatcherInterface::class,
            $this->behatMatcher->integer('1'),
        );
    }

    #[Test]
    public function matchesDateTimeISO8601(): void
    {
        $this->matcherMock
            ->expects($this->once())
            ->method('dateTimeISO8601')
            ->with($this->isType('string'))
            ->willReturn($this->createMock(Regex::class));

        $this->assertInstanceOf(
            MatcherInterface::class,
            $this->behatMatcher->dateTimeISO8601('2018-05-11T11:00:00+00:00'),
        );
    }

    #[Test]
    public function matchesBoolean(): void
    {
        $this->matcherMock
            ->expects($this->once())
            ->method('boolean')
            ->willReturn($this->createMock(Type::class));

        $this->assertInstanceOf(
            MatcherInterface::class,
            $this->behatMatcher->boolean('true'),
        );
    }

    #[Test]
    public function matchesUuid(): void
    {
        $this->matcherMock
            ->expects($this->once())
            ->method('uuid')
            ->with($this->isType('string'))
            ->willReturn($this->createMock(Regex::class));

        $this->assertInstanceOf(
            MatcherInterface::class,
            $this->behatMatcher->uuid('string'),
        );
    }

    #[Test]
    public function matchesEachLike(): void
    {
        $this->matcherMock
            ->expects($this->once())
            ->method('eachLike')
            ->with($this->isType('array'))
            ->willReturn($this->createMock(MinType::class));

        $this->assertInstanceOf(
            MatcherInterface::class,
            $this->behatMatcher->eachLike(['object' => 'structure']),
        );
    }
}
