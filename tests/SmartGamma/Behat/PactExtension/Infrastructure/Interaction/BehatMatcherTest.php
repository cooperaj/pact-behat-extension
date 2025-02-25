<?php

declare(strict_types=1);

namespace Tests\SmartGamma\Behat\PactExtension\Infrastructure\Interaction;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use SmartGamma\Behat\PactExtension\Infrastructure\Interaction\BehatMatcher;
use PhpPact\Consumer\Matcher\Matcher;

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

    public function testMatchesExact(): void
    {
        $this->assertIsString($this->behatMatcher->exact('my value'));
    }

    public function testMatchesLike(): void
    {
        $this->matcherMock->expects($this->once())->method('like')->willReturn(['PactMatcherLikeDummy']);
        $this->assertIsIterable($this->behatMatcher->like('my value'));
    }

    public function testMatchesInteger(): void
    {
        $this->matcherMock
            ->expects($this->once())
            ->method('integer')
            ->with($this->isType('int'))
            ->willReturn(['PactMatcherIntegerDummy']);
        $this->assertIsIterable($this->behatMatcher->integer('1'));
    }

    public function testMatchesDateTimeISO8601(): void
    {
        $this->matcherMock
            ->expects($this->once())
            ->method('dateTimeISO8601')
            ->willReturn(['PactMatcherDateDummy']);
        $this->assertIsIterable($this->behatMatcher->dateTimeISO8601('2018-05-11T11:00:00+00:00'));
    }

    public function testMatchesBoolean(): void
    {
        $this->matcherMock->expects($this->once())->method('boolean')->willReturn(['PactMatcherBooleanDummy']);
        $this->assertIsIterable($this->behatMatcher->boolean('true'));
    }

    public function testMatchesUuid(): void
    {
        $this->matcherMock->expects($this->once())->method('uuid')->willReturn(['PactMatcherUuidDummy']);
        $this->assertIsIterable($this->behatMatcher->uuid('string'));
    }

    public function testMatchesEachLike(): void
    {
        $this->matcherMock
            ->expects($this->once())
            ->method('eachLike')
            ->with($this->isType('array'))
            ->willReturn(['PactMatcherEachLikeDummy']);
        $this->assertIsIterable($this->behatMatcher->eachLike(['object' => 'structure']));
    }
}