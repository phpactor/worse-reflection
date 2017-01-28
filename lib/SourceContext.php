<?php

declare(strict_types=1);

namespace DTL\WorseReflection;

use DTL\WorseReflection\ClassName;
use PhpParser\Parser;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;

class SourceContext
{
    private $namespaceNode;
    private $classNodes = [];

    public function __construct(Source $source, Parser $parser)
    {
        $statements = $parser->parse($source->getSource());

        // get namespace
        foreach ($statements as $statement) {
            if ($statement instanceof Namespace_) {
                $this->namespaceNode = $statement;
                break;
            }
        }

        if ($this->namespaceNode) {
            $this->scanClassNodes($this->namespaceNode->stmts);
        } else {
            $this->scanClassNodes($statements);
        }
    }

    public function hasClass(ClassName $className): bool
    {
        return isset($this->classNodes[$className->getFqn()]);
    }

    public function getClassNode(ClassName $className): Class_
    {
        if (false === $this->hasClass($className)) {
            throw new \InvalidArgumentException(sprintf(
                'Source context does not contain class "%s", it has classes: ["%s"]',
                $className->getFqn(), implode('", "', array_keys($this->classNodes))
            ));
        }

        return $this->classNodes[$className->getFqn()];
    }

    private function scanClassNodes(array $nodes)
    {
        foreach ($nodes as $node) {
            if ($node instanceof Class_) {
                $this->classNodes[$node->name] = $node;
            }
        }
    }
}
