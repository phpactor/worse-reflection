<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor\DocblockParser;

use Phpactor\DocblockParser\Ast\TypeNode;
use Phpactor\DocblockParser\Ast\Type\ScalarNode;
use Phpactor\Docblock\DocblockTypes;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\IntType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\StringType;
use Phpactor\WorseReflection\Core\Types;

class TypeConverter
{
    public function convert(?TypeNode $type): Type
    {
        if ($type instanceof ScalarNode) {
            if ($type->toString() === 'int') {
                return new IntType();
            }
            if ($type->toString() === 'string') {
                return new StringType();
            }
        }

        return new MissingType();
    }
}
