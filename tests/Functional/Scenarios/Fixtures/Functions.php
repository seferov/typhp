<?php

namespace Seferov\Typhp\Tests\Functional\Scenarios\Fixtures;

function foo(string $a): bool
{
    return true;
}

function bar($a) {}

/**
 * @param mixed $a
 */
function mixedArgument($a) {}

/**
 * @return mixed
 */
function mixedReturn($a) {
    return $a;
}
