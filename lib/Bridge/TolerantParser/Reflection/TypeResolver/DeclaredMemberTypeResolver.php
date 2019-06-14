<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\TypeResolver;

use Microsoft\PhpParser\Token;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\ClassName;
use Microsoft\PhpParser\Node;

class DeclaredMemberTypeResolver
{
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
        
        $name = $tolerantType->getResolvedName();
        
        if ($className && $name === 'self') {
            return Type::fromString((string) $className);
        }
        
        return Type::fromString($name);
    }
}
