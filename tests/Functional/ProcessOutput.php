<?php

namespace Seferov\Typhp\Tests\Functional;

class ProcessOutput
{
    /**
     * @var array
     */
    private $lines;
    /**
     * @var int
     */
    private $exitCode;

    public function __construct(array $lines, int $exitCode)
    {
        $this->lines = $lines;
        $this->exitCode = $exitCode;
    }

    public function getLines(): array
    {
        return $this->lines;
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }
}
