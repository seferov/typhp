<?php

namespace Seferov\Typhp\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Seferov\Typhp\Analyser;
use Seferov\Typhp\Issue\IssueInterface;
use Seferov\Typhp\IssueCollection;

class AnalyserTest extends TestCase
{
    public function testAnalyse(): void
    {
        $code = '<?php
            function foo($bar) {
                return $bar > 0;
            }
            
            $foo = function ($foo) {
                return $foo;
            };
            
            array_map($foo, [0, 1, 2]);
            
            class ClosuresWithIssues
            {
                public $foo;
            
                public function foo($foo)
                {
                    $this->foo = $foo;
                
                    $foo(1);
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
            
            (new ClosuresWithIssues())->foo(function ($bar) {
                return $bar > 1;
            });
        ';

        $analyser = new Analyser($code);
        $issueCollection = $analyser->analyse();
        $this->assertInstanceOf(IssueCollection::class, $issueCollection);
        $this->assertCount(8, $issueCollection);

        $this->assertInstanceOf(IssueInterface::class, $issueCollection->current());
    }
}
