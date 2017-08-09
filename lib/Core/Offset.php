<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\WorseReflection\Core\Offset;

final class Offset
{
    private $offset;

    private function __construct(int $offset)
    {
        if ($offset < 0) {
            throw new \InvalidArgumentException(sprintf(
                'Offset cannot be negative! Got "%s"',
                $offset
            ));
        }

        $this->offset = $offset;
    }

    public static function fromInt(int $offset): Offset
    {
         return new self($offset);
    }

    public function toInt()
    {
        return $this->offset;
    }
}
