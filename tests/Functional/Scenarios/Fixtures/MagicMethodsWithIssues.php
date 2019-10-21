<?php

namespace Seferov\Typhp\Tests\Functional\Scenarios\Fixtures;

class MagicMethodsWithIssues
{
    public function __construct($name) {}

    public function __destruct() {}

    public function __call($name, $arguments)
    {
        return $name;
    }

    public static function __callStatic($name, $arguments)
    {
        return $name;
    }

    public function __get($name)
    {
        return false;
    }

    public function __set($name, $value) {}

    public function __isset($name)
    {
        return false;
    }

    public function __unset($name) {}

    public function __sleep()
    {
        return [];
    }

    public function __wakeup() {}

    public function __toString()
    {
        return 'string';
    }
    public function __invoke($flag) {
        return 'invoked';
    }
    public static function __set_state($properties) {
        return new self;
    }
    public function __clone() {}
    public function __debugInfo()
    {
        return [];
    }
}
