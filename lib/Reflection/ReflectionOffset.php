<?php

namespace Phpactor\WorseReflection\Reflection;

use Phpactor\WorseReflection\Reflection\Inference\Value;
use Phpactor\WorseReflection\Reflection\Inference\Frame;

final class ReflectionOffset
{
    private $frame;
    private $value;

    private function __construct(Frame $frame, Value $value)
    {
        $this->frame = $frame;
        $this->value = $value;
    }

    public static function fromFrameAndValue($frame, $value)
    {
        return new self($frame, $value);
    }

    public function frame(): Frame
    {
        return $this->frame;
    }

    public function value(): Value
    {
        return $this->value;
    }
}
