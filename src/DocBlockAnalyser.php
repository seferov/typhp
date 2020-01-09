<?php

namespace Seferov\Typhp;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Object_;

class DocBlockAnalyser
{
    public function isSuppressedByInheritDoc(DocBlock $docBlock): bool
    {
        if ('{@inheritdoc}' === strtolower($docBlock->getSummary())) {
            return true;
        }

        foreach ($docBlock->getDescription()->getTags() as $tag) {
            if ($tag instanceof Generic && 'inheritdoc' === strtolower($tag->getName())) {
                return true;
            }
        }

        return false;
    }

    public function isVarSuppressedByDocBlock(DocBlock $docBlock): bool
    {
        $varTags = $docBlock->getTagsByName('var');
        if (empty($varTags)) {
            return false;
        }

        /** @var Var_ $varTag */
        $varTag = $varTags[0];

        return $this->isTypeSuppressed($varTag->getType());
    }

    public function isParamSuppressedByDocBlock(string $paramName, DocBlock $docBlock): bool
    {
        /** @var DocBlock\Tags\Param[] $paramTags */
        $paramTags = $docBlock->getTagsByName('param');
        foreach ($paramTags as $paramTag) {
            if ($paramTag->getVariableName() !== $paramName) {
                continue;
            }

            return $this->isTypeSuppressed($paramTag->getType());
        }

        return false;
    }

    public function isReturnSuppressedByDocBlock(DocBlock $docBlock): bool
    {
        $returnTags = $docBlock->getTagsByName('return');
        if (empty($returnTags)) {
            return false;
        }

        /** @var Return_ $returnTag */
        $returnTag = $returnTags[0];

        return $this->isTypeSuppressed($returnTag->getType());
    }

    private function isTypeSuppressed(?Type $type): bool
    {
        if (!$type) {
            return false;
        }

        if ($type instanceof Mixed_) {
            return true;
        }

        if ($type instanceof Object_ && !$type->getFqsen()) {
            return true;
        }

        if ($type instanceof Compound) {
            if (2 === $type->getIterator()->count()) {
                // Ex: string|null => ?string
                foreach ($type as $t) {
                    if ($t instanceof Null_) {
                        return false;
                    }
                }
            }

            return true;
        }

        return false;
    }
}
