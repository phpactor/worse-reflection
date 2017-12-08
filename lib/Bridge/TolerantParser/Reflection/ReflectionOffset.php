<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\Inference\SymbolContext;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset as CoreReflectionOffset;

final class ReflectionOffset implements CoreReflectionOffset
{
    /**
     * @var Frame
     */
    private $frame;

    /**
     * @var SymbolContext
     */
    private $symbolInformation;

    private function __construct(Frame $frame, SymbolContext $symbolInformation)
    {
        $this->frame = $frame;
        $this->symbolInformation = $symbolInformation;
    }

    public static function fromFrameAndSymbolContext($frame, $symbolInformation)
    {
        return new self($frame, $symbolInformation);
    }

    public function frame(): Frame
    {
        return $this->frame;
    }

    public function symbolInformation(): SymbolContext
    {
        return $this->symbolInformation;
    }
}
