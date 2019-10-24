<?php

namespace Seferov\Typhp\Issue;

class UntypedKnownArgumentIssue extends AbstractIssue
{
    /**
     * @var string
     */
    private $argumentName;
    /**
     * @var string
     */
    private $type;

    public static function create(string $name, int $line, string $argumentName, string $type): parent
    {
        $issue = new self;
        $issue->name = $name;
        $issue->line = $line;
        $issue->argumentName = $argumentName;
        $issue->type = $type;

        return $issue;
    }

    public function getIssueCompact(): string
    {
        return implode(';', [$this->line, $this->name, 'untyped-known-argument', $this->argumentName]);
    }

    public function getIssue(): string
    {
        return sprintf('Missing type declaration for argument "%s". Type must be "%s"', $this->argumentName, $this->type);
    }
}
