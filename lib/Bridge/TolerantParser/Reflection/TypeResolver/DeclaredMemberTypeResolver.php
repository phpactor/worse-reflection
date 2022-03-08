<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\TypeResolver;

use Microsoft\PhpParser\Node\DelimitedList\QualifiedNameList;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\TokenKind;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\ClassName;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\Util\QualifiedNameListUtil;

class DeclaredMemberTypeResolver
{
    private const RESERVED_NAMES = [
        'iterable',
        'resource',
    ];

    /**
     */
    public function resolveTypes(Node $tolerantNode, $declaredTypes = null, ClassName $className = null, bool $nullable = false): Types
    {
        if (!$declaredTypes instanceof QualifiedNameList) {
            return Types::empty();
        }

        return Types::fromTypes(array_filter(array_map(function ($tolerantType = null) use ($tolerantNode, $className, $nullable) {
            if ($tolerantType instanceof Token && $tolerantType->kind === TokenKind::BarToken) {
                return false;
            }
            return $this->resolve($tolerantNode, $tolerantType, $className, $nullable);
        }, $declaredTypes->children)));
    }

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

        if ($tolerantType instanceof QualifiedNameList) {
            $tolerantType = QualifiedNameListUtil::firstQualifiedNameOrToken($tolerantType);
        }

        if ($tolerantType instanceof Token) {
            $text = $tolerantType->getText($tolerantNode->getFileContents());

            return Type::fromString($text);
        }

        $text = $tolerantType->getText($tolerantNode->getFileContents());
        if ($tolerantType->isUnqualifiedName() && in_array($text, self::RESERVED_NAMES)) {
            return type::fromString($text);
        }

        $name = $tolerantType->getResolvedName();
        if ($className && $name === 'self') {
            return Type::fromString((string) $className);
        }

        return Type::fromString($name);
    }
}
