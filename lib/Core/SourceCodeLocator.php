<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\WorseReflection\Core\Exception\SourceNotFound;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\SourceCode;

interface SourceCodeLocator
{
    /**
     * @throws SourceNotFound
     */
    public function locate(ClassName $className): SourceCode;
}
