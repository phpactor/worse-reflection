<?php

namespace Phpactor\WorseReflection\Core\DocBlock;

interface DocBlock
{
    public function isDefined(): bool;

    public function raw(): string;

    public function formatted(): string;

    public function returnTypes(): array;

    public function methodTypes(string $methodName): array;

    public function vars(): DocBlockVars;

    public function inherits(): bool;
}
