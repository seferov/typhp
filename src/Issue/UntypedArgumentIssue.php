<?php

namespace Seferov\Typhp\Issue;

class UntypedArgumentIssue extends AbstractIssue
{
    /**
     * @var string
     */
    private $argumentName;

    public static function create(string $name, int $line, string $argumentName): self
    {
        $issue = new self;
        $issue->name = $name;
        $issue->line = $line;
        $issue->argumentName = $argumentName;

        return $issue;
    }

    public function getIssueCode(): string
    {
        return 'untyped-argument';
    }

    public function getIssue(): string
    {
        return sprintf('Missing type declaration for argument "%s"', $this->argumentName);
    }

    public function getArgumentName(): string
    {
        return $this->argumentName;
    }
}
