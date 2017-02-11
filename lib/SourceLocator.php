<?php

namespace DTL\WorseReflection;


interface SourceLocator
{
    public function locate(ClassName $className): Source;
}
