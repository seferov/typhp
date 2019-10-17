<?php

namespace Seferov\Typhp\Tests\Functional\Scenarios\Fixtures;

class ClassConstruct
{
    public function __construct() {}
}

class ClassConstruct2
{
    public function __construct($a) {}
}

class ClassConstruct3
{
    public function __construct($a, string $b) {}
}
