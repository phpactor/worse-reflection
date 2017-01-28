<?php

declare(strict_types=1);

namespace DTL\WorseReflection;

use DTL\WorseReflection\ClassName;
use DTL\WorseReflection\Namespace_ as WorseNamespace;
use PhpParser\Parser;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;

class SourceContext
{
    private $namespaceNode;
    private $classNodes = [];
    private $useNodes = [];

    public function __construct(Source $source, Parser $parser)
    {
        $statements = $parser->parse($source->getSource());
        $this->scanNamespace($statements);

        if (null === $this->namespaceNode) {
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
            throw new \RuntimeException(sprintf(
                'Source context does not contain class "%s", it has classes: ["%s"]',
                $className->getFqn(), implode('", "', array_keys($this->classNodes))
            ));
        }

        return $this->classNodes[$className->getFqn()];
    }

    public function getNamespace()
    {
        if (null === $this->namespaceNode) {
            return WorseNamespace::fromParts([]);
        }

        return WorseNamespace::fromParts($this->namespaceNode->name->parts);
    }

    public function resolveClassName(string $classShortName): ClassName
    {
        if (isset($this->useNodes[$classShortName])) {
            $useNode = $this->useNodes[$classShortName];
            return ClassName::fromFqnParts($useNode->name->parts);
        }

        return $this->getNamespace()->spawnClassName($classShortName);
    }

    private function scanClassNodes(array $nodes)
    {
        foreach ($nodes as $node) {
            if ($node instanceof Class_) {
                $this->classNodes[$node->name] = $node;
            }

            if ($node instanceof Use_) {
                foreach ($node->uses as $use) {
                    $this->useNodes[$use->alias] = $use;
                }
            }

        }
    }

    private function scanNamespace(array $nodes)
    {
        // get namespace
        foreach ($nodes as $node) {
            if ($node instanceof Namespace_) {
                $this->namespaceNode = $node;
                $this->scanClassNodes($node->stmts);
            }
        }
    }
}
