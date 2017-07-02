<?php

namespace DTL\WorseReflection;

use DTL\WorseReflection\SourceCode;


interface SourceCodeLocator
{
    public function locate(ClassName $className): SourceCode;
}
