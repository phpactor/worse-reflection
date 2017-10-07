<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Docblock;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionConstantCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionInterfaceCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionTraitCollection;

interface ReflectionClass extends ReflectionClassLike
{
    public function isAbstract(): bool;

    public function constants(): ReflectionConstantCollection;

    public function parent();

    public function properties(): ReflectionPropertyCollection;

    public function interfaces(): ReflectionInterfaceCollection;

    public function traits(): ReflectionTraitCollection;

    public function memberListPosition(): Position;

    public function isInstanceOf(ClassName $className): bool;
}
