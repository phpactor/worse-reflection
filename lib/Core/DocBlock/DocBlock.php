<?php

namespace Phpactor\WorseReflection\Core\DocBlock;

use Phpactor\WorseReflection\Core\Types;

interface DocBlock
{
    public function isDefined(): bool;

    public function raw(): string;

    public function formatted(): string;

    public function returnTypes(): Types;

    public function methodTypes(string $methodName): Types;

    public function propertyTypes(string $methodName): Types;

    public function parameterTypes(string $paramName): Types;

    public function vars(): DocBlockVars;

    public function inherits(): bool;
}
