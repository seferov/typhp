<?php

namespace Seferov\Typhp\Tests\Functional\Scenarios\Fixtures;

class InheritDoc
{
    /**
     * Comment
     *
     * {@inheritDoc}
     */
    public function foo($a)
    {
        return $a;
    }
    /**
     * {@inheritDoc}
     */
    public function bar($a)
    {
        return $a;
    }
}
