<?php

namespace Phpactor\WorseReflection\Core;

interface DocblockInspector
{
    public function typesForMethod(string $docblock, string $methodName): array;
}
