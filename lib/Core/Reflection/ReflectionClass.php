<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\SourceCode;
use bool;
use Phpactor\WorseReflection\Core\Docblock;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionConstantCollection;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionInterfaceCollection;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection\ReflectionTraitCollection;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionClass;

interface ReflectionClass
{
    public function position(): Position;

    public function name(): ClassName;

    public function methods(ReflectionClass $contextClass = null): ReflectionMethodCollection;

    public function sourceCode(): SourceCode;

    public function isInterface(): bool;

    public function isTrait(): bool;

    public function isClass(): bool;

    public function isConcrete();

    public function docblock(): Docblock;

    public function isAbstract(): bool;

    public function constants(): ReflectionConstantCollection;

    public function parent();

    public function properties(): ReflectionPropertyCollection;

    public function interfaces(): ReflectionInterfaceCollection;

    public function traits(): ReflectionTraitCollection;

    public function memberListPosition(): Position;

    public function isInstanceOf(ClassName $className): bool;
}