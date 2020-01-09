<?php

namespace Seferov\Typhp\Tests\Functional\Scenarios\Fixtures;

class PropertyNoIssues
{
    private string $string1;

    /**
     * @var string
     */
    private string $string2;

    /**
     * @var {@inheritdoc}
     */
    private string $string3;

    /**
     * @var string|null
     */
    private ?string $mixed4;
}