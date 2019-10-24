<?php

namespace Seferov\Typhp\Tests\Unit;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use PHPUnit\Framework\TestCase;
use Seferov\Typhp\DocBlockAnalyser;

/**
 * @coversDefaultClass \Seferov\Typhp\DocBlockAnalyser
 */
class DocBlockAnalyserTest extends TestCase
{
    /**
     * @covers ::isParamSuppressedByDocBlock
     */
    public function testParamSuppressed(): void
    {
        $docComment = '
        /**
         * @param mixed $foo
         * @param object $bar
         * @param object|array $baz
         */
        ';

        $docBlockAnalyser = new DocBlockAnalyser();
        $docBlock = $this->getDocBlock($docComment);
        $this->assertTrue($docBlockAnalyser->isParamSuppressedByDocBlock('foo', $docBlock));
        $this->assertTrue($docBlockAnalyser->isParamSuppressedByDocBlock('bar', $docBlock));
        $this->assertTrue($docBlockAnalyser->isParamSuppressedByDocBlock('baz', $docBlock));
    }

    /**
     * @covers ::isSuppressedByInheritDoc
     * @dataProvider InheritParamSuppressedData
     */
    public function testInheritParamSuppressed(string $docComment): void
    {
        $docBlockAnalyser = new DocBlockAnalyser();
        $this->assertTrue($docBlockAnalyser->isSuppressedByInheritDoc($this->getDocBlock($docComment)));
    }

    public function InheritParamSuppressedData(): array
    {
        return [
            [
                '/**
                  * {@inheritDoc}
                  */'
            ],
            [
                '/**
                  * Lorem ipsum
                  *
                  * {@inheritDoc}
                  */'
            ],
            [
                '/**
                  * {@inheritDoc}
                  *
                  * Lorem ipsum
                  */'
            ],
        ];
    }

    /**
     * @covers ::isSuppressedByInheritDoc
     */
    public function testInheritParamNotSuppressed(): void
    {
        $docBlockAnalyser = new DocBlockAnalyser();
        $this->assertFalse($docBlockAnalyser->isSuppressedByInheritDoc($this->getDocBlock('/** @inheritDoc */')));
    }


    /**
     * @covers ::isParamSuppressedByDocBlock
     */
    public function testParamNotSuppressed(): void
    {
        $docComment = '
        /**
         * @param string $foo
         * @param DocBlock $bar
         * @param array|null $baz
         * @param $variable
         */
        ';

        $docBlockAnalyser = new DocBlockAnalyser();
        $docBlock = $this->getDocBlock($docComment);
        $this->assertFalse($docBlockAnalyser->isParamSuppressedByDocBlock('foo', $docBlock));
        $this->assertFalse($docBlockAnalyser->isParamSuppressedByDocBlock('bar', $docBlock));
        $this->assertFalse($docBlockAnalyser->isParamSuppressedByDocBlock('baz', $docBlock));
        $this->assertFalse($docBlockAnalyser->isParamSuppressedByDocBlock('variable', $docBlock));
        $this->assertFalse($docBlockAnalyser->isParamSuppressedByDocBlock('variableDoesNotExistInDoc', $docBlock));
    }

    /**
     * @covers ::isReturnSuppressedByDocBlock
     */
    public function testReturnSuppressed(): void
    {
        $docBlockAnalyser = new DocBlockAnalyser();

        $this->assertTrue($docBlockAnalyser->isReturnSuppressedByDocBlock($this->getDocBlock('/** @return mixed */')));
        $this->assertTrue($docBlockAnalyser->isReturnSuppressedByDocBlock($this->getDocBlock('/** @return object */')));
        $this->assertTrue($docBlockAnalyser->isReturnSuppressedByDocBlock($this->getDocBlock('/** @return bool|int */')));
    }

    /**
     * @covers ::isReturnSuppressedByDocBlock
     */
    public function testReturnNotSuppressed(): void
    {
        $docBlockAnalyser = new DocBlockAnalyser();

        $this->assertFalse($docBlockAnalyser->isReturnSuppressedByDocBlock($this->getDocBlock('/** @return string */')));
        $this->assertFalse($docBlockAnalyser->isReturnSuppressedByDocBlock($this->getDocBlock('/** @return int */')));
        $this->assertFalse($docBlockAnalyser->isReturnSuppressedByDocBlock($this->getDocBlock('/** @return DocBlock */')));
        $this->assertFalse($docBlockAnalyser->isReturnSuppressedByDocBlock($this->getDocBlock('/** @return array|null */')));
        $this->assertFalse($docBlockAnalyser->isReturnSuppressedByDocBlock($this->getDocBlock('/** @return int|null */')));
        $this->assertFalse($docBlockAnalyser->isReturnSuppressedByDocBlock($this->getDocBlock('/** @param int $foo */')));
    }

    private function getDocBlock(string $docComment): DocBlock
    {
        return (DocBlockFactory::createInstance())->create($docComment);
    }
}
