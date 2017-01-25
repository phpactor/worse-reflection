<?php

namespace DTL\WorseReflection;

interface ClassLocator
{
    public function locate(ClassName $className);
}
