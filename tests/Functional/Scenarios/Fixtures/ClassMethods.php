<?php

namespace Seferov\Typhp\Tests\Functional\Scenarios\Fixtures;

class ClassMethods
{
    public function foo() {}

    public function bar($a) {}

    public function a(int $a, $b) {}

    public function b(string $a, string $b): bool
    {
        return $a === $b;
    }

    /**
     * @param mixed $a
     */
    public function c($a, string $b): bool
    {
        return true;
    }
}
