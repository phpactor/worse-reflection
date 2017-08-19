<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Reflection\Inference\SymbolInformation;
use Phpactor\WorseReflection\Core\Reflection\Inference\Frame;

final class ReflectionOffset
{
    /**
     * @var Frame
     */
    private $frame;
    /**
     * @var Value
     */
    private $value;

    private function __construct(Frame $frame, SymbolInformation $value)
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

    public function symbol(): Symbol
    {
        return $this->value;
    }

    public function type(): Type
    {
        return $this->type;
    }

    public function value(): SymbolInformation
    {
        return $this->value;
    }
}
