<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection;

use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection as CoreReflectionParameterCollection;
use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Phpactor\WorseReflection\Core\Inference\Frame;

/**
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionParameter get()
 */
class ReflectionArgumentCollection extends AbstractReflectionCollection implements CoreReflectionParameterCollection
{
    public static function fromArgumentListAndFrame(ServiceLocator $locator, ArgumentExpressionList $list, Frame $frame)
    {
        $arguments = [];
        foreach ($list->getElements() as $element) {
            $arguments[] = new ReflectionArgument($locator, $frame, $element);
        }

        return new self($locator, $arguments);
    }
}
