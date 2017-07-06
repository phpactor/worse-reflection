<?php

namespace Phpactor\WorseReflection;

interface SourceCodeLocator
{
    public function locate(ClassName $className): SourceCode;
}
