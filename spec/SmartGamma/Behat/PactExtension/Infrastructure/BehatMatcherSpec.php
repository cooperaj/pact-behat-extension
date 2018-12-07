<?php

namespace spec\SmartGamma\Behat\PactExtension\Infrastructure;

use SmartGamma\Behat\PactExtension\Infrastructure\BehatMatcher;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PhpPact\Consumer\Matcher\Matcher;

class BehatMatcherSpec extends ObjectBehavior
{
    public function let(Matcher $matcher)
    {
        $matcher->like(Argument::any())->willReturn(['PactMatcherLikeDummy']);
        $matcher->integer(Argument::type('int'))->willReturn(['PactMatcherIntegerDummy']);
        $matcher->dateTimeISO8601(Argument::any())->willReturn(['PactMatcherDateDummy']);
        $matcher->boolean(Argument::any())->willReturn(['PactMatcherBoolenDummy']);
        $matcher->uuid(Argument::any())->willReturn(['PactMatcherUuidDummy']);
        $matcher->eachLike(Argument::type('array'))->willReturn(['PactMatcherEachLikeDummy']);

        $this->beConstructedWith($matcher);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(BehatMatcher::class);
    }

    public function it_matches_exact()
    {
        $this->exact('my value')->shouldBeString();

    }

    public function it_matches_like()
    {
        $this->like('my value')->shouldBeArray();
    }

    public function it_matches_integer()
    {
        $this->integer('1')->shouldBeArray();
    }

    public function it_matches_dateTimeISO8601()
    {
        $this->dateTimeISO8601('2018-05-11T11:00:00+00:00')->shouldBeArray();
    }

    public function it_matches_boolean()
    {
        $this->boolean('true')->shouldBeArray();
    }

    public function it_matches_uuid()
    {
        $this->uuid('string')->shouldBeArray();
    }

    public function it_matches_each_like()
    {
        $this->eachLike(['object' => 'structure'])->shouldBeArray();
    }
}