<?php

namespace Phpactor\WorseReflection\Tests\Integration\Util;

class CodeHelper
{
    public static function offsetFromCode($source)
    {
        $offset = $offset = strpos($source, '<>');
        if (!$offset) {
            throw new \InvalidArgumentException(sprintf(
                'Could not find offset <> in example code: %s',
                $source
            ));
        }

        $source = substr($source, 0, $offset) . substr($source, $offset + 2);

        return [$source, $offset];
    }
}
