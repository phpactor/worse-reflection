<?php

namespace Phpactor\WorseReflection;

use Phpactor\WorseReflection\Exception\SourceNotFound;

interface SourceCodeLocator
{
    /**
     * @throws SourceNotFound
     */
    public function locate(ClassName $className): SourceCode;
}
