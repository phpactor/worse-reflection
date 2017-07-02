<?php

namespace DTL\WorseReflection;

interface SourceCodeLocator
{
    public function locate(ClassName $className): SourceCode;
}
