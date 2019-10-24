<?php

namespace Seferov\Typhp\Tests\Functional\Scenarios\Fixtures;

$foo = function (int $foo): int {
    return $foo;
};

array_map($foo, [0, 1, 2]);

array_filter([0, 1, 2], function (int $a): bool {
    return $a > 1;
});

class ClosuresNoIssues
{
    public function foo(callable $foo): void
    {
        $foo(1);
    }
}

(new ClosuresNoIssues())->foo(function (int $bar): bool {
    return $bar > 1;
});
