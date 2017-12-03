<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\WorseReflection\Reflector;

/**
 * @method ClassName fromString()
 * @method ClassName fromunknown()
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
