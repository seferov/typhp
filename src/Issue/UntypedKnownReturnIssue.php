<?php

namespace Seferov\Typhp\Issue;

class UntypedKnownReturnIssue extends AbstractIssue
{
    /**
     * @var string
     */
    private $type;

    public static function create(string $name, int $line, string $type): self
    {
        $issue = new self;
        $issue->name = $name;
        $issue->line = $line;
        $issue->type = $type;

        return $issue;
    }

    public function getIssueCompact(): string
    {
        return implode(';', [$this->line, $this->name, 'untyped-known-return']);
    }

    public function getIssue(): string
    {
        return sprintf('Missing return type. Type must be "%s"', $this->type);
    }
}
