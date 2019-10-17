<?php

namespace Seferov\Typhp\Tests\Functional;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class FunctionalTestCase extends TestCase
{
    protected function process(string $path): ProcessOutput
    {
        $process = new Process(['bin/typhp', 'analyse', $path, '--format=compact']);
        $process->run();

        return new ProcessOutput(
            array_slice(explode("\n", $process->getOutput()), 3, -3),
            $process->getExitCode()
        );
    }
}
