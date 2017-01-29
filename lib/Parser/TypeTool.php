<?php

namespace DTL\WorseReflection\Parser;

use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\DNumber;

class TypeTool
{
    public static function resolveScalarTypeValue(Scalar $type)
    {
        var_dump($type->value);die();;
        if ($type instanceof String_) {
            return (string) $value;
        }

        if ($type instanceof LNumber) {
            return (float) $value;
        }

        if ($type instanceof DNumber) {
            return (int) $value;
        }

        return $value;
    }
}
