<?php

namespace Phpactor\WorseReflection;

use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Reflection\ReflectionClass;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Phpactor\WorseReflection\Reflection\AbstractReflectionClass;
use Microsoft\PhpParser\Node\Statement\NamespaceDefinition;

class DocblockResolver
{
    public function methodReturnTypeFromNodeDocblock(AbstractReflectionClass $class, MethodDeclaration $node)
    {
        if (Type::unknown() != $type = $this->typeFromNode($node, 'return')) {
            return $type;
        }

        if (preg_match('#inheritdoc#i', $node->getLeadingCommentAndWhitespaceText())) {
            if ($class->parent()) {
                if ($class->parent()->methods()->has($node->getName())) {
                    return $class->parent()->methods()->get($node->getName())->inferredReturnType();
                }
            }
        }

        return Type::unknown();
    }

    public function propertyType(PropertyDeclaration $node)
    {
        return $this->typeFromNode($node, 'var');
    }

    public function nodeType(Node $node)
    {
        return $this->typeFromNode($node, 'var');
    }

    private function typeFromNode(Node $node, string $tag)
    {
        $comment = $node->getLeadingCommentAndWhitespaceText();

        if (!preg_match(sprintf('{@%s ([\w+\\\]+)}', $tag), $comment, $matches)) {
            return Type::unknown();
        }

        if (substr($matches[1], 0, 1) == '\\') {
            return Type::fromString($matches[1]);
        }

        $typeString = trim($matches[1], '\\');
        $parts = explode('\\', $typeString);

        $importTable = $node->getImportTablesForCurrentScope()[0];
        $firstPart = array_shift($parts);

        if (isset($importTable[$firstPart])) {
            return Type::fromString($importTable[$firstPart].'\\'.implode('\\', $parts));
        }

        // TODO: Test default namespace
        $namespace = $node->getRoot()->getFirstChildNode(NamespaceDefinition::class);

        if (null === $namespace) {
            return Type::fromString($matches[1]);
        }

        return Type::fromArray([(string) $namespace->name, $matches[1]]);
    }
}
