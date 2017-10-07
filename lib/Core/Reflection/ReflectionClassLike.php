<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Docblock;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;

interface ReflectionClassLike
{
    public function position(): Position;

    public function name(): ClassName;

    public function methods(): ReflectionMethodCollection;

    public function sourceCode(): SourceCode;

    public function isInterface(): bool;

    public function isTrait(): bool;

    public function isClass(): bool;

    public function isConcrete();

    public function docblock(): Docblock;
}

