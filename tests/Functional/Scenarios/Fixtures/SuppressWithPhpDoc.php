<?php

namespace Seferov\Typhp\Tests\Functional\Scenarios\Fixtures;

use Exception;

class SuppressWithPhpDoc
{
    /**
     * @var object
     */
    private $a;

    /**
     * @param object $a
     */
    public function __construct($a)
    {
        $this->a = $a;
    }

    /**
     * @return object
     */
    public function getA()
    {
        return $this->a;
    }

    /**
     * @return mixed
     */
    public function foo()
    {
        return $this->a->b;
    }

    /**
     * {@inheritDoc}
     */
    public function inheritdoc()
    {
        return $this->foo();
    }

    /**
     * @return object|array
     */
    public function multiple()
    {
        return $this->foo();
    }
}

/**
 * @param object $a
 * @return mixed
 */
function bar($a) {
    return $a->b;
}

interface SuppressWithPhpDocInterface
{
    /**
     * @param mixed $a
     *
     * @return object
     *
     * @throws Exception
     */
    public function foo($a);
}
