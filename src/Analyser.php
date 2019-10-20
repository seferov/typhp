<?php

namespace Seferov\Typhp;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\ParserFactory;
use Seferov\Typhp\Issue\UntypedArgumentIssue;
use Seferov\Typhp\Issue\UntypedKnownArgumentIssue;
use Seferov\Typhp\Issue\UntypedKnownReturnIssue;
use Seferov\Typhp\Issue\UntypedReturnIssue;

class Analyser
{
    /**
     * @var string
     */
    private $code;
    /**
     * @var string
     */
    private $fileName;
    /**
     * @var IssueCollection
     */
    private $issueCollection;
    /**
     * @var DocBlockFactory
     */
    private $docBlockFactory;
    /**
     * @var DocBlockAnalyser
     */
    private $docBlockAnalyser;

    public function __construct(string $fileName, string $code)
    {
        $this->code = $code;
        $this->fileName = $fileName;
        $this->issueCollection = new IssueCollection();
        $this->docBlockFactory  = DocBlockFactory::createInstance();
        $this->docBlockAnalyser  = new DocBlockAnalyser();
    }

    public function analyse(): IssueCollection
    {
        $this->issueCollection->empty();

        $parser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7);
        $ast = $parser->parse($this->code);

        foreach ($ast as $node) {
            $this->analyseNode($node);
        }

        return $this->issueCollection;
    }

    private function analyseNode(Node $node): void
    {
        if ($node instanceof Node\FunctionLike) {
            $this->analyseFunctionLike($node);
        }

        if (isset($node->stmts)) {
            foreach ($node->stmts as $subNode) {
                $this->analyseNode($subNode);
            }
        }
    }

    private function analyseFunctionLike(Node\FunctionLike $functionLike): void
    {
        if (!$functionLike instanceof Node\Stmt\ClassMethod && !$functionLike instanceof Node\Stmt\Function_) {
            // todo: support closures and arrow functions
            return;
        }

        $name = $functionLike->name;

        $docBlock = null;
        if ($functionLike->getDocComment()) {
            try {
                $docBlock = $this->docBlockFactory->create($functionLike->getDocComment()->getText());
            } catch (\Exception $e) {
                // Invalid phpdoc case; continue analyzing without phpdoc info.
            }
        }

        if ($docBlock && $this->docBlockAnalyser->isSuppressedByInheritDoc($docBlock)) {
            return;
        }

        if ($functionLike instanceof Node\Stmt\ClassMethod && in_array($name->name, ['__construct', '__destruct', '__call', '__callStatic', '__get', '__set', '__isset', '__unset', '__sleep', '__wakeup', '__toString', '__invoke', '__set_state', '__clone', '__debugInfo'])) {
            $this->analyseSpecialMagicMethods($functionLike, $docBlock);
            return;
        }

        $this->analyseParams($functionLike->getParams(), $name, $docBlock);
        $this->analyseReturn($functionLike->getReturnType(), $name, $docBlock);
    }

    /**
     * @param Node\Param[] $params
     */
    private function analyseParams(array $params, Node\Identifier $name, ?DocBlock $docBlock): void
    {
        foreach ($params as $param) {
            if (null !== $param->type) {
                continue;
            }

            if ($docBlock && $this->docBlockAnalyser->isParamSuppressedByDocBlock($param->var->name, $docBlock)) {
                continue;
            }

            $this->issueCollection->add(UntypedArgumentIssue::create($name->name, $name->getStartLine(), $param->var->name));
        }
    }

    /**
     * @param null|Identifier|Node\Name|Node\NullableType $returnType
     */
    private function analyseReturn($returnType, Node\Identifier $name, ?DocBlock $docBlock): void
    {
        if (null !== $returnType) {
            return;
        }

        if ($docBlock && $this->docBlockAnalyser->isReturnSuppressedByDocBlock($docBlock)) {
            return;
        }

        $this->issueCollection->add(UntypedReturnIssue::create($name->name, $name->getStartLine()));
    }

    private function analyseSpecialMagicMethods(Node\Stmt\ClassMethod $classMethod, ?DocBlock $docBlock): void
    {
        $name = $classMethod->name;
        switch ($name->name) {
            case '__construct':
            case '__invoke':
                $this->analyseParams($classMethod->getParams(), $name, $docBlock);
                break;
            case '__call':
            case '__callStatic':
                // string $name, array $arguments. ANALYSE
                // mixed return type. ANALYSE
                $params = $classMethod->getParams();
                $firstParam = array_shift($params);
                if (null === $firstParam->type) {
                    $this->issueCollection->add(UntypedKnownArgumentIssue::create($name->name, $name->getStartLine(), $firstParam->var->name, 'string'));
                }
                $secondParam = array_shift($params);
                if (null === $secondParam->type) {
                    $this->issueCollection->add(UntypedKnownArgumentIssue::create($name->name, $name->getStartLine(), $secondParam->var->name, 'array'));
                }
                $this->analyseReturn($classMethod->getReturnType(), $name, $docBlock);
                break;
            case '__get':
                $params = $classMethod->getParams();
                if (null === $params[0]->type) {
                    $this->issueCollection->add(UntypedKnownArgumentIssue::create($name->name, $name->getStartLine(), $params[0]->var->name, 'string'));
                }
                $this->analyseReturn($classMethod->getReturnType(), $name, $docBlock);
                break;
            case '__set':
                $params = $classMethod->getParams();
                $firstParam = array_shift($params);
                if (null === $firstParam->type) {
                    $this->issueCollection->add(UntypedKnownArgumentIssue::create($name->name, $name->getStartLine(), $firstParam->var->name, 'string'));
                }
                $this->analyseParams($params, $name, $docBlock);
                $this->analyseReturn($classMethod->getReturnType(), $name, $docBlock);
                break;
            case '__unset':
                $params = $classMethod->getParams();
                $firstParam = array_shift($params);
                if (null === $firstParam->type) {
                    $this->issueCollection->add(UntypedKnownArgumentIssue::create($name->name, $name->getStartLine(), $firstParam->var->name, 'string'));
                }
                $return = $classMethod->getReturnType();
                if (null === $return) {
                    $this->issueCollection->add(UntypedKnownReturnIssue::create($name->name, $name->getStartLine(), 'void'));
                }
                break;
            case '__sleep':
            case '__debugInfo':
                $return = $classMethod->getReturnType();
                if (null === $return) {
                    $this->issueCollection->add(UntypedKnownReturnIssue::create($name->name, $name->getStartLine(), 'array'));
                }
                break;
            case '__wakeup':
                $return = $classMethod->getReturnType();
                if (null === $return) {
                    $this->issueCollection->add(UntypedKnownReturnIssue::create($name->name, $name->getStartLine(), 'void'));
                }
                break;
            case '__toString':
                $return = $classMethod->getReturnType();
                if (null === $return) {
                    $this->issueCollection->add(UntypedKnownReturnIssue::create($name->name, $name->getStartLine(), 'string'));
                }
                break;
            case '__isset':
                $params = $classMethod->getParams();
                if (null === $params[0]->type) {
                    $this->issueCollection->add(UntypedKnownArgumentIssue::create($name->name, $name->getStartLine(), $params[0]->var->name, 'string'));
                }
                $return = $classMethod->getReturnType();
                if (null === $return) {
                    $this->issueCollection->add(UntypedKnownReturnIssue::create($name->name, $name->getStartLine(), 'bool'));
                }
                break;
            case '__set_state':
                $params = $classMethod->getParams();
                if (null === $params[0]->type) {
                    $this->issueCollection->add(UntypedKnownArgumentIssue::create($name->name, $name->getStartLine(), $params[0]->var->name, 'array'));
                }
                break;
            case '__destruct':
            case '__clone':
                break;
        }
    }
}
