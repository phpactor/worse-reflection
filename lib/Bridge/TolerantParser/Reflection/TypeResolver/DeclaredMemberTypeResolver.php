<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\TypeResolver;

use Microsoft\PhpParser\Token;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\ClassName;
use Microsoft\PhpParser\Node;

class DeclaredMemberTypeResolver
{
    public function resolve(ClassName $className, Node $tolerantNode, $tolerantType = null): Type
    {
        if (null === $tolerantType) {
            return Type::undefined();
        }

        if ($tolerantType instanceof Token) {
            $text = $tolerantType->getText($tolerantNode->getFileContents());

            return Type::fromString($text);
        }

        $name = $tolerantType->getResolvedName();

        if ($name === 'self') {
            return Type::fromString((string) $className);
        }

        return Type::fromString($name);
    }
}
