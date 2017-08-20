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
     * @var SymbolInformation
     */
    private $symbolInformation;

    private function __construct(Frame $frame, SymbolInformation $symbolInformation)
    {
        $this->frame = $frame;
        $this->symbolInformation = $symbolInformation;
    }

    public static function fromFrameAndValue($frame, $symbolInformation)
    {
        return new self($frame, $symbolInformation);
    }

    public function frame(): Frame
    {
        return $this->frame;
    }

    public function symbol(): Symbol
    {
        return $this->symbolInformation;
    }

    public function symbolInformation(): SymbolInformation
    {
        return $this->symbolInformation;
    }
}
