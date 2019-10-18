<?php

namespace Seferov\Typhp;

use phpDocumentor\Reflection\DocBlockFactory;
use PhpParser\Node;
use PhpParser\ParserFactory;
use Seferov\Typhp\Issue\UntypedArgumentIssue;
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
            }
        }

        if ($docBlock && $this->docBlockAnalyser->isSuppressedByInheritDoc($docBlock)) {
            return;
        }

        foreach ($functionLike->getParams() as $param) {
            if (null === $param->type) {
                if ($docBlock && $this->docBlockAnalyser->isParamSuppressedByDocBlock($param->var->name, $docBlock)) {
                    continue;
                }

                $this->issueCollection->add(UntypedArgumentIssue::create($name->name, $name->getStartLine(), $param->var->name));
            }
        }

        if (null === $functionLike->getReturnType() && '__construct' !== $name->name) {
            if ($docBlock && $this->docBlockAnalyser->isReturnSuppressedByDocBlock($docBlock)) {
                return;
            }

            $this->issueCollection->add(UntypedReturnIssue::create($name->name, $name->getStartLine()));
        }
    }
}
