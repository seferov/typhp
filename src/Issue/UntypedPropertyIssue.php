<?php

namespace Seferov\Typhp\Issue;

class UntypedPropertyIssue extends AbstractIssue
{
    public static function create(string $name, int $line): self
    {
        $issue = new self;
        $issue->name = $name;
        $issue->line = $line;

        return $issue;
    }

    public function getIssueCompact(): string
    {
        return implode(';', [$this->line, $this->name, 'untyped-property']);
    }

    public function getIssue(): string
    {
        return 'untyped property';
    }
}
