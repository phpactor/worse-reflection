<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Symbol;
use Phpactor\WorseReflection\Core\Inference\SymbolInformation;

interface ReflectionOffset
{
    public static function fromFrameAndSymbolInformation($frame, $symbolInformation);

    public function frame(): Frame;

    public function symbol(): Symbol;

    public function symbolInformation(): SymbolInformation;
}
