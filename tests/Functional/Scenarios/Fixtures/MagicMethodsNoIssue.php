<?php

namespace Seferov\Typhp\Tests\Functional\Scenarios\Fixtures;

class MagicMethodsNoIssue
{
    public function __construct(string $name) {}

    public function __destruct() {}

    public function __call(string $name, array $arguments): string
    {
        return $name;
    }

    public static function __callStatic(string $name, array $arguments): string
    {
        return $name;
    }

    public function __get(string $name): bool
    {
        return false;
    }

    /**
     * @param mixed $value
     */
    public function __set(string $name, $value): void {}

    public function __isset(string $name): bool
    {
        return false;
    }

    public function __unset(string $name): void {}

    public function __sleep(): array
    {
        return [];
    }

    public function __wakeup(): void {}

    public function __toString(): string
    {
        return 'string';
    }
    public function __invoke(bool $flag) {
        return 'invoked';
    }
    public static function __set_state(array $properties) {
        return new self;
    }
    public function __clone() {}
    public function __debugInfo(): array
    {
        return [];
    }
}
