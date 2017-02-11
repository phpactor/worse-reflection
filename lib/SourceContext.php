<?php

declare(strict_types=1);

namespace DTL\WorseReflection;

use DTL\WorseReflection\ClassName;
use DTL\WorseReflection\Namespace_ as WorseNamespace;
use PhpParser\Parser;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_ as ParserNamespace;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Builder\Declaration;
use PhpParser\Node\Stmt\ClassLike;

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

    public function getNamespace()
    {
        if (null === $this->namespaceNode) {
            return WorseNamespace::fromParts([]);
        }

        return WorseNamespace::fromParts($this->namespaceNode->name->parts);
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
                $this->classNodes[$node->name] = $node;
            }
            if ($node instanceof GroupUse) {
                $namespace = WorseNamespace::fromParts($node->prefix->parts);
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

    private function scanNamespace(array $nodes)
    {
        // get namespace
        foreach ($nodes as $node) {
            if ($node instanceof ParserNamespace) {
                $this->namespaceNode = $node;
                $this->scanClassNodes($node->stmts);
            }
        }
    }
}
