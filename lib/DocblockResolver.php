<?php

namespace DTL\WorseReflection;

use Microsoft\PhpParser\Node;
use DTL\WorseReflection\Type;
use DTL\WorseReflection\Reflection\ReflectionClass;
use Microsoft\PhpParser\Node\MethodDeclaration;

class DocblockResolver
{
    public function methodReturnTypeFromNodeDocblock(ReflectionClass $class, MethodDeclaration $node)
    {
        $comment = $node->getLeadingCommentAndWhitespaceText();

        if (preg_match('{@return ([\w+\\\]+)}', $comment, $matches)) {
            if (substr($matches[1], 0, 1) == '\\') {
                return Type::fromString($matches[1]);
            }

            $typeString = trim($matches[1], '\\');
            $parts = explode('\\', $typeString);

            $importTable = $node->getImportTablesForCurrentScope()[0];
            $firstPart = array_shift($parts);

            if (isset($importTable[$firstPart])) {
                return Type::fromString($importTable[$firstPart] . '\\' . implode('\\', $parts));
            }

            return Type::fromString($matches[1]);
        }

        if (preg_match('#inheritdoc#i', $comment, $matches)) {
            return $class->parent()->methods()->get($node->getName())->type();
        }

        return Type::unknown();
    }

}
