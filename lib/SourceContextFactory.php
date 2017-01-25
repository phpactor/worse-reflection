<?php

namespace DTL\WorseReflection;

interface SourceContextFactory
{
    public function createFor(Source $source);

    public function hasClass($argument1);
}
