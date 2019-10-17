<?php

namespace Seferov\Typhp\Issue;

abstract class AbstractIssue implements IssueInterface
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var int
     */
    protected $line;

    protected function __construct()
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLine(): int
    {
        return $this->line;
    }
}
