<?php

namespace DTL\WorseReflection;

use DTL\WorseReflection\Source;

interface SourceLocator
{
    public function locate(ClassName $className): Source;
}
