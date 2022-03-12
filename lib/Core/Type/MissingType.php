<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

final class MissingType implements Type
{
    public function __toString(): string
    {
        return '<missing>';
    }

    public function toPhpString(): string
    {
        return '';
    }
}
