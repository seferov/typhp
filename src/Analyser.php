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
        } elseif ($node instanceof Node\Stmt\Expression) {
            $this->analyseNode($node->expr);
        } elseif ($node instanceof Node\Expr\FuncCall || $node instanceof Node\Expr\MethodCall) {
            foreach ($node->args as $arg) {
                $this->analyseNode($arg->value);
            }
        } elseif ($node instanceof Node\Expr\Assign) {
            $this->analyseNode($node->expr);
        }

        if (isset($node->stmts)) {
            foreach ($node->stmts as $subNode) {
                $this->analyseNode($subNode);
            }
        }
    }

    private function analyseFunctionLike(Node\FunctionLike $functionLike): void
    {
        if ($functionLike instanceof Node\Stmt\ClassMethod || $functionLike instanceof Node\Stmt\Function_) {
            $name = $functionLike->name->name;
            $line = $functionLike->name->getStartLine();
        } elseif ($functionLike instanceof Node\Expr\Closure) {
            $name = 'n/a (closure)';
            $line = $functionLike->getStartLine();
        } else {
            // todo: support arrow functions (PHP 7.4)
            return;
        }

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

        if ($functionLike instanceof Node\Stmt\ClassMethod && in_array($name, ['__construct', '__destruct', '__call', '__callStatic', '__get', '__set', '__isset', '__unset', '__sleep', '__wakeup', '__toString', '__invoke', '__set_state', '__clone', '__debugInfo'])) {
            $this->analyseSpecialMagicMethods($functionLike, $docBlock);
            return;
        }

        $this->analyseParams($functionLike->getParams(), $name, $line, $docBlock);
        $this->analyseReturn($functionLike->getReturnType(), $name, $line, $docBlock);
    }

    /**
     * @param Node\Param[] $params
     */
    private function analyseParams(array $params, string $name, int $line, ?DocBlock $docBlock): void
    {
        foreach ($params as $param) {
            if (null !== $param->type) {
                continue;
            }

            if ($docBlock && $this->docBlockAnalyser->isParamSuppressedByDocBlock($param->var->name, $docBlock)) {
                continue;
            }

            $this->issueCollection->add(UntypedArgumentIssue::create($name, $line, $param->var->name));
        }
    }

    /**
     * @param null|Identifier|Node\Name|Node\NullableType $returnType
     */
    private function analyseReturn($returnType, string $name, int $line, ?DocBlock $docBlock): void
    {
        if (null !== $returnType) {
            return;
        }

        if ($docBlock && $this->docBlockAnalyser->isReturnSuppressedByDocBlock($docBlock)) {
            return;
        }

        $this->issueCollection->add(UntypedReturnIssue::create($name, $line));
    }

    private function analyseSpecialMagicMethods(Node\Stmt\ClassMethod $classMethod, ?DocBlock $docBlock): void
    {
        $name = $classMethod->name->name;
        $line = $classMethod->name->getStartLine();
        switch ($name) {
            case '__construct':
            case '__invoke':
                $this->analyseParams($classMethod->getParams(), $name, $line, $docBlock);
                break;
            case '__call':
            case '__callStatic':
                $params = $classMethod->getParams();
                $firstParam = array_shift($params);
                if (null === $firstParam->type) {
                    $this->issueCollection->add(UntypedKnownArgumentIssue::create($name, $line, $firstParam->var->name, 'string'));
                }
                $secondParam = array_shift($params);
                if (null === $secondParam->type) {
                    $this->issueCollection->add(UntypedKnownArgumentIssue::create($name, $line, $secondParam->var->name, 'array'));
                }
                $this->analyseReturn($classMethod->getReturnType(), $name, $line, $docBlock);
                break;
            case '__get':
                $params = $classMethod->getParams();
                if (null === $params[0]->type) {
                    $this->issueCollection->add(UntypedKnownArgumentIssue::create($name, $line, $params[0]->var->name, 'string'));
                }
                $this->analyseReturn($classMethod->getReturnType(), $name, $line, $docBlock);
                break;
            case '__set':
                $params = $classMethod->getParams();
                $firstParam = array_shift($params);
                if (null === $firstParam->type) {
                    $this->issueCollection->add(UntypedKnownArgumentIssue::create($name, $line, $firstParam->var->name, 'string'));
                }
                $this->analyseParams($params, $name, $line, $docBlock);
                $this->analyseReturn($classMethod->getReturnType(), $name, $line, $docBlock);
                break;
            case '__unset':
                $params = $classMethod->getParams();
                $firstParam = array_shift($params);
                if (null === $firstParam->type) {
                    $this->issueCollection->add(UntypedKnownArgumentIssue::create($name, $line, $firstParam->var->name, 'string'));
                }
                $return = $classMethod->getReturnType();
                if (null === $return) {
                    $this->issueCollection->add(UntypedKnownReturnIssue::create($name, $line, 'void'));
                }
                break;
            case '__sleep':
            case '__debugInfo':
                $return = $classMethod->getReturnType();
                if (null === $return) {
                    $this->issueCollection->add(UntypedKnownReturnIssue::create($name, $line, 'array'));
                }
                break;
            case '__wakeup':
                $return = $classMethod->getReturnType();
                if (null === $return) {
                    $this->issueCollection->add(UntypedKnownReturnIssue::create($name, $line, 'void'));
                }
                break;
            case '__toString':
                $return = $classMethod->getReturnType();
                if (null === $return) {
                    $this->issueCollection->add(UntypedKnownReturnIssue::create($name, $line, 'string'));
                }
                break;
            case '__isset':
                $params = $classMethod->getParams();
                if (null === $params[0]->type) {
                    $this->issueCollection->add(UntypedKnownArgumentIssue::create($name, $line, $params[0]->var->name, 'string'));
                }
                $return = $classMethod->getReturnType();
                if (null === $return) {
                    $this->issueCollection->add(UntypedKnownReturnIssue::create($name, $line, 'bool'));
                }
                break;
            case '__set_state':
                $params = $classMethod->getParams();
                if (null === $params[0]->type) {
                    $this->issueCollection->add(UntypedKnownArgumentIssue::create($name, $line, $params[0]->var->name, 'array'));
                }
                break;
            case '__destruct':
            case '__clone':
                break;
        }
    }
}
