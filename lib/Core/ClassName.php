<?php

namespace Phpactor\WorseReflection\Core;

/**
 * @method static ClassName fromString(string $name)
 * @method static ClassName fromunknown($unknown)
 */
class ClassName extends Name
{
    public function namespace(): string
    {
        if (count($this->parts) === 1) {
            return '';
        }

        return implode('\\', array_slice($this->parts, 0, count($this->parts) - 1));
    }
}
