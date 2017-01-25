<?php

namespace DTL\WorseReflection;

use DTL\WorseReflection\ClassName;

interface SourceContext
{
    public function hasClass(ClassName $className);
}
