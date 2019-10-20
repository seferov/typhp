<?php

namespace Seferov\Typhp\Tests\Functional\Scenarios;

use Seferov\Typhp\Tests\Functional\FunctionalTestCase;

class MagicMethodsTest extends FunctionalTestCase
{
    public function testNoIssues(): void
    {
        $output = $this->process('tests/Functional/Scenarios/Fixtures/MagicMethodsNoIssue.php');
        $outputLines = $output->getLines();
        $this->assertCount(0, $outputLines);
        $this->assertSame(0, $output->getExitCode());
    }

    public function testWithIssues(): void
    {
        $output = $this->process('tests/Functional/Scenarios/Fixtures/MagicMethodsWithIssues.php');
        $outputLines = $output->getLines();
        $this->assertSame(4, $output->getExitCode());
        $this->assertCount(22, $outputLines);

        $this->assertSame('7;__construct;untyped-argument;name', array_shift($outputLines));
        $this->assertSame('11;__call;untyped-known-argument;name', array_shift($outputLines));
        $this->assertSame('11;__call;untyped-known-argument;arguments', array_shift($outputLines));
        $this->assertSame('11;__call;untyped-return', array_shift($outputLines));
        $this->assertSame('16;__callStatic;untyped-known-argument;name', array_shift($outputLines));
        $this->assertSame('16;__callStatic;untyped-known-argument;arguments', array_shift($outputLines));
        $this->assertSame('16;__callStatic;untyped-return', array_shift($outputLines));
        $this->assertSame('21;__get;untyped-known-argument;name', array_shift($outputLines));
        $this->assertSame('21;__get;untyped-return', array_shift($outputLines));
        $this->assertSame('26;__set;untyped-known-argument;name', array_shift($outputLines));
        $this->assertSame('26;__set;untyped-argument;value', array_shift($outputLines));
        $this->assertSame('26;__set;untyped-return', array_shift($outputLines));
        $this->assertSame('28;__isset;untyped-known-argument;name', array_shift($outputLines));
        $this->assertSame('28;__isset;untyped-known-return', array_shift($outputLines));
        $this->assertSame('33;__unset;untyped-known-argument;name', array_shift($outputLines));
        $this->assertSame('33;__unset;untyped-known-return', array_shift($outputLines));
        $this->assertSame('35;__sleep;untyped-known-return', array_shift($outputLines));
        $this->assertSame('40;__wakeup;untyped-known-return', array_shift($outputLines));
        $this->assertSame('42;__toString;untyped-known-return', array_shift($outputLines));
        $this->assertSame('46;__invoke;untyped-argument;flag', array_shift($outputLines));
        $this->assertSame('49;__set_state;untyped-known-argument;properties', array_shift($outputLines));
        $this->assertSame('53;__debugInfo;untyped-known-return', array_shift($outputLines));
    }
}
