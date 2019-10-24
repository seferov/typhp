<?php

namespace Seferov\Typhp\Tests\Functional\Scenarios;

use Seferov\Typhp\Tests\Functional\FunctionalTestCase;

class ClosuresTest extends FunctionalTestCase
{
    public function testNoIssues(): void
    {
        $output = $this->process('tests/Functional/Scenarios/Fixtures/ClosuresNoIssues.php');
        $outputLines = $output->getLines();
        $this->assertCount(0, $outputLines);
        $this->assertSame(0, $output->getExitCode());
    }

    public function testWithIssues(): void
    {
        $output = $this->process('tests/Functional/Scenarios/Fixtures/ClosuresWithIssues.php');
        $outputLines = $output->getLines();
        $this->assertCount(8, $outputLines);
        $this->assertSame(4, $output->getExitCode());

        $this->assertSame('5;n/a (closure);untyped-argument;foo', array_shift($outputLines));
        $this->assertSame('5;n/a (closure);untyped-return', array_shift($outputLines));
        $this->assertSame('11;n/a (closure);untyped-argument;a', array_shift($outputLines));
        $this->assertSame('11;n/a (closure);untyped-return', array_shift($outputLines));
        $this->assertSame('17;foo;untyped-argument;foo', array_shift($outputLines));
        $this->assertSame('17;foo;untyped-return', array_shift($outputLines));
        $this->assertSame('23;n/a (closure);untyped-argument;bar', array_shift($outputLines));
        $this->assertSame('23;n/a (closure);untyped-return', array_shift($outputLines));
    }
}