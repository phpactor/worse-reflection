<?php

namespace Phpactor\WorseReflection;

use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Reflection\ReflectionClass;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Phpactor\WorseReflection\Reflection\AbstractReflectionClass;

class DocblockResolver
{
    public function methodReturnTypeFromNodeDocblock(AbstractReflectionClass $class, MethodDeclaration $node)
    {
        if (Type::unknown() != $type = $this->typeFromNode($node, 'return')) {
            return $type;
        }

        if (preg_match('#inheritdoc#i', $node->getLeadingCommentAndWhitespaceText())) {
            return $class->parent()->methods()->get($node->getName())->type();
        }

        return Type::unknown();
    }

    public function propertyType(PropertyDeclaration $node)
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

        return Type::fromString($matches[1]);
    }
}
