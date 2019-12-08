<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\TypeResolver;

use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Token;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\ClassName;
use Microsoft\PhpParser\Node;

class DeclaredMemberTypeResolver
{
    private const WRONG_QUALIFIED_NAME = [
        'iterable',
        'resource',
    ];

    public function resolve(Node $tolerantNode, $tolerantType = null, ClassName $className = null, bool $nullable = false): Type
    {
        $type = $this->doResolve($tolerantType, $tolerantNode, $className);
        if ($nullable) {
            return $type->asNullable();
        }
        return $type;
    }

    private function doResolve($tolerantType, ?Node $tolerantNode, ?ClassName $className): Type
    {
        if (null === $tolerantType) {
            return Type::undefined();
        }

        if ($tolerantType instanceof Token) {
            $text = $tolerantType->getText($tolerantNode->getFileContents());

            return Type::fromString($text);
        }

        /** @var QualifiedName $tolerantType */
        $text = $tolerantType->getText($tolerantNode->getFileContents());
        if ($tolerantType->isUnqualifiedName() && in_array($text, self::WRONG_QUALIFIED_NAME)) {
            return type::fromString($text);
        }

        $name = $tolerantType->getResolvedName();
        if ($className && $name === 'self') {
            return Type::fromString((string) $className);
        }

        return Type::fromString($name);
    }
}
