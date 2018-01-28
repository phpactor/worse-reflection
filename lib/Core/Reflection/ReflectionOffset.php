<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\SymbolContext;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;

interface ReflectionOffset
{
    public static function fromFrameAndSymbolContext($frame, $symbolInformation);

    public function frame(): Frame;

    public function symbolContext(): SymbolContext;
}
