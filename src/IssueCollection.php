<?php

namespace Seferov\Typhp;

use Seferov\Typhp\Issue\IssueInterface;

class IssueCollection implements \Countable, \Iterator
{
    /**
     * @var array
     */
    private $issues;

    public function __construct()
    {
        $this->issues = [];
    }

    public function add(IssueInterface $issue): void
    {
        $this->issues[] = $issue;
    }

    public function empty(): void
    {
        $this->issues = [];
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return current($this->issues);
    }

    /**
     * @return mixed
     */
    public function next()
    {
        return next($this->issues);
    }

    /**
     * @return int|string|null
     */
    public function key()
    {
        return key($this->issues);
    }

    public function count(): int
    {
        return count($this->issues);
    }

    public function valid(): bool
    {
        return false !== $this->current();
    }

    /**
     * @return mixed
     */
    public function rewind()
    {
        return reset($this->issues);
    }
}
