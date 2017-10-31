<?php

namespace Phpactor\WorseReflection\Core;

class ClassName extends FullyQualifiedName
{
    public function namespace(): string
    {
        if (count($this->parts) === 1) {
            return '';
        }

        return implode('\\', array_slice($this->parts, 0, count($this->parts) - 1));
    }
}
