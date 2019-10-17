<?php

namespace Seferov\Typhp\Tests\Functional\Scenarios;

use Seferov\Typhp\Tests\Functional\FunctionalTestCase;

class SuppressWithPhpDocTest extends FunctionalTestCase
{
    public function testSuppress()
    {
        $output = $this->process('tests/Functional/Scenarios/Fixtures/SuppressWithPhpDoc.php');
        $this->assertSame(0, $output->getExitCode());
    }
}
