<?php

namespace Seferov\Typhp\Tests\Functional\Scenarios;

use Seferov\Typhp\Tests\Functional\FunctionalTestCase;

class PropertyTest extends FunctionalTestCase
{
    public function testNoIssues(): void
    {
        $output = $this->process('tests/Functional/Scenarios/Fixtures/PropertyNoIssues.php');
        $outputLines = $output->getLines();
        $this->assertCount(0, $outputLines);
        $this->assertSame(0, $output->getExitCode());
    }

    public function testWithIssues(): void
    {
        $output = $this->process('tests/Functional/Scenarios/Fixtures/PropertyWithIssues.php');
        $outputLines = $output->getLines();
        $this->assertSame(4, $output->getExitCode());
        $this->assertCount(1, $outputLines);

        $this->assertSame('7;private;untyped-property;name', array_shift($outputLines));
    }
}