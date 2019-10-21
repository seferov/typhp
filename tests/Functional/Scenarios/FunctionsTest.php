<?php

namespace Seferov\Typhp\Tests\Functional\Scenarios;

use Seferov\Typhp\Tests\Functional\FunctionalTestCase;

class FunctionsTest extends FunctionalTestCase
{
    public function testConstruct(): void
    {
        $output = $this->process('tests/Functional/Scenarios/Fixtures/Functions.php');
        $outputLines = $output->getLines();
        $this->assertCount(4, $outputLines);
        $this->assertSame('10;bar;untyped-argument;a', $outputLines[0]);
        $this->assertSame('10;bar;untyped-return', $outputLines[1]);
        $this->assertSame('15;mixedArgument;untyped-return', $outputLines[2]);
        $this->assertSame('20;mixedReturn;untyped-argument;a', $outputLines[3]);
        $this->assertSame(4, $output->getExitCode());
    }
}
