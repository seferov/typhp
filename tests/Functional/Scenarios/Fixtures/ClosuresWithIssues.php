<?php

namespace Seferov\Typhp\Tests\Functional\Scenarios\Fixtures;

$foo = function ($foo) {
    return $foo;
};

array_map($foo, [0, 1, 2]);

array_filter([0, 1, 2], function ($a) {
    return $a > 1;
});

class ClosuresWithIssues
{
    public function foo($foo)
    {
        $foo(1);
    }
}

(new ClosuresWithIssues())->foo(function ($bar) {
    return $bar > 1;
});
