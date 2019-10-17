<?php

namespace Seferov\Typhp\Tests\Unit;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use PHPUnit\Framework\TestCase;
use Seferov\Typhp\DocBlockAnalyser;

class DocBlockAnalyserTest extends TestCase
{
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

    public function testParamNotSuppressed(): void
    {
        $docComment = '
        /**
         * @param string $foo
         * @param DocBlock $bar
         * @param array|null $baz
         */
        ';

        $docBlockAnalyser = new DocBlockAnalyser();
        $docBlock = $this->getDocBlock($docComment);
        $this->assertFalse($docBlockAnalyser->isParamSuppressedByDocBlock('foo', $docBlock));
        $this->assertFalse($docBlockAnalyser->isParamSuppressedByDocBlock('bar', $docBlock));
        $this->assertFalse($docBlockAnalyser->isParamSuppressedByDocBlock('baz', $docBlock));
    }

    public function testReturnSuppressed(): void
    {
        $docBlockAnalyser = new DocBlockAnalyser();

        $this->assertTrue($docBlockAnalyser->isReturnSuppressedByDocBlock($this->getDocBlock('/** @return mixed */')));
        $this->assertTrue($docBlockAnalyser->isReturnSuppressedByDocBlock($this->getDocBlock('/** @return object */')));
        $this->assertTrue($docBlockAnalyser->isReturnSuppressedByDocBlock($this->getDocBlock('/** @return object|return */')));
    }

    public function testReturnNotSuppressed(): void
    {
        $docBlockAnalyser = new DocBlockAnalyser();

        $this->assertFalse($docBlockAnalyser->isReturnSuppressedByDocBlock($this->getDocBlock('/** @return string */')));
        $this->assertFalse($docBlockAnalyser->isReturnSuppressedByDocBlock($this->getDocBlock('/** @return int */')));
        $this->assertFalse($docBlockAnalyser->isReturnSuppressedByDocBlock($this->getDocBlock('/** @return DocBlock */')));
        $this->assertFalse($docBlockAnalyser->isReturnSuppressedByDocBlock($this->getDocBlock('/** @return array|null */')));
        $this->assertFalse($docBlockAnalyser->isReturnSuppressedByDocBlock($this->getDocBlock('/** @return int|null */')));
    }

    private function getDocBlock(string $docComment): DocBlock
    {
        return (DocBlockFactory::createInstance())->create($docComment);
    }
}
