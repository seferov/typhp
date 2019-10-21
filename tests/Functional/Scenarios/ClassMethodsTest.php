<?php

namespace Seferov\Typhp\Tests\Functional\Scenarios;

use Seferov\Typhp\Tests\Functional\FunctionalTestCase;

class ClassMethodsTest extends FunctionalTestCase
{
    public function testConstruct(): void
    {
        $output = $this->process('tests/Functional/Scenarios/Fixtures/ClassConstruct.php');
        $outputLines = $output->getLines();
        $this->assertCount(2, $outputLines);
        $this->assertSame('12;__construct;untyped-argument;a', $outputLines[0]);
        $this->assertSame('17;__construct;untyped-argument;a', $outputLines[1]);
        $this->assertSame(4, $output->getExitCode());
    }

    public function testMethods(): void
    {
        $output = $this->process('tests/Functional/Scenarios/Fixtures/ClassMethods.php');
        $outputLines = $output->getLines();
        $this->assertCount(5, $outputLines);
        $this->assertSame('7;foo;untyped-return', $outputLines[0]);
        $this->assertSame('9;bar;untyped-argument;a', $outputLines[1]);
        $this->assertSame('9;bar;untyped-return', $outputLines[2]);
        $this->assertSame('11;a;untyped-argument;b', $outputLines[3]);
        $this->assertSame('11;a;untyped-return', $outputLines[4]);
        $this->assertSame(4, $output->getExitCode());
    }
}
