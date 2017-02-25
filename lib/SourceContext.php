<?php

declare(strict_types=1);

namespace DTL\WorseReflection;

use PhpParser\Parser;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\ErrorHandler\Collecting;

class SourceContext
{
    private $namespaceNode;
    private $classNodes = [];
    private $useNodes = [];
    private $nodes;

    public function __construct(Source $source, Parser $parser)
    {
        $this->nodes = $parser->parse($source->getSource(), new Collecting());
        $this->scanNamespace();

        if (null === $this->namespaceNode) {
            $this->scanClassNodes($this->nodes);
        }
    }

    public function getNodes(): array
    {
        return $this->nodes;
    }

    public function hasClass(ClassName $className): bool
    {
        return isset($this->classNodes[$className->getFqn()]);
    }

    public function getClassNode(ClassName $className): ClassLike
    {
        if (false === $this->hasClass($className)) {
            throw new \RuntimeException(sprintf(
                'Source context does not contain class "%s", it has classes: ["%s"]',
                $className->getFqn(), implode('", "', array_keys($this->classNodes))
            ));
        }

        return $this->classNodes[$className->getFqn()];
    }

    public function getTraverser()
    {
        return new AstTraverser($this->nodes);
    }

    public function getNamespace()
    {
        if (null === $this->namespaceNode) {
            return NamespaceName::fromParts([]);
        }

        return NamespaceName::fromParts($this->namespaceNode->name->parts);
    }

    public function resolveClassName(ClassName $className): ClassName
    {
        if (isset($this->useNodes[$className->getShortName()])) {
            return $this->useNodes[$className->getShortName()];
        }

        return $this->getNamespace()->spawnClassName($className->getShortName());
    }

    private function scanClassNodes(array $nodes)
    {
        foreach ($nodes as $node) {
            if ($node instanceof Class_ || $node instanceof Interface_) {
                $this->classNodes[$this->resolveClassName(ClassName::fromString($node->name))->getFqn()] = $node;
            }
            if ($node instanceof GroupUse) {
                $namespace = NamespaceName::fromParts($node->prefix->parts);
                foreach ($node->uses as $use) {
                    $this->useNodes[$use->alias] = ClassName::fromNamespaceAndShortName($namespace, (string) $use->name);
                }
            }

            if ($node instanceof Use_) {
                foreach ($node->uses as $use) {
                    $this->useNodes[$use->alias] = ClassName::fromParts($use->name->parts);
                }
            }

        }
    }

    private function scanNamespace()
    {
        foreach ($this->nodes as $node) {
            if ($node instanceof Namespace_) {
                $this->namespaceNode = $node;
                $this->scanClassNodes($node->stmts);
            }
        }
    }
}
