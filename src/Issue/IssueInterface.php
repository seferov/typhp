<?php

namespace Seferov\Typhp\Issue;

interface IssueInterface
{
    public function getName(): string;

    public function getLine(): int;

    public function getIssue(): string;

    public function getIssueCode(): string;
}
