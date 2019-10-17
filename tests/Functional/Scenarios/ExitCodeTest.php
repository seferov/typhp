<?php

namespace Seferov\Typhp\Tests\Functional\Scenarios;

use Seferov\Typhp\Tests\Functional\FunctionalTestCase;

class ExitCodeTest extends FunctionalTestCase
{
    public function testNoIssue()
    {
        $output = $this->process('tests/Functional/Scenarios/Fixtures/ExitCodeNoIssue.php');
        $this->assertSame(0, $output->getExitCode());
    }
}
