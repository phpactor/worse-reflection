<?php

namespace Phpactor\WorseReflection\Core\Reflector;

use Phpactor\WorseReflection\Core\Diagnostics;

interface Analyzer
{
    public function analyze($sourceCode): Diagnostics;
}
