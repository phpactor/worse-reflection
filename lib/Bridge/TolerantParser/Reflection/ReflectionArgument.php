<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Phpactor\WorseReflection\Core\Position;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Microsoft\PhpParser\Node\Expression\Variable;
use Phpactor\WorseReflection\Core\Inference\SymbolContext;
use Phpactor\WorseReflection\Core\Reflection\ReflectionArgument as CoreReflectionArgument;
use RuntimeException;
use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;

class ReflectionArgument implements CoreReflectionArgument
{
    /**
     * @var ServiceLocator
     */
    private $services;

    /**
     * @var ArgumentExpression
     */
    private $node;

    /**
     * @var Frame
     */
    private $frame;

    public function __construct(ServiceLocator $services, Frame $frame, ArgumentExpression $node)
    {
        $this->services = $services;
        $this->node = $node;
        $this->frame = $frame;
    }

    public function guessName(): string
    {
        if ($this->node->expression instanceof Variable) {
            $name = $this->node->expression->name->getText($this->node->getFileContents());

            if (is_string($name) && substr($name, 0, 1) == '$') {
                return substr($name, 1);
            }

            return $name;
        }

        if ($this->type()->isDefined()) {
            $typeName = $this->type()->isClass() ? (string) $this->type()->className()->short() : (string) $this->type();
            return lcfirst($typeName);
        }


        return 'argument' . $this->index();
    }

    public function type(): Type
    {
        return $this->info()->type();
    }

    public function value()
    {
        return $this->info()->value();
    }

    public function position(): Position
    {
        return Position::fromFullStartStartAndEnd(
            $this->node->getFullStartPosition(),
            $this->node->getStartPosition(),
            $this->node->getEndPosition()
        );
    }

    private function info(): SymbolContext
    {
        return $this->services->symbolContextResolver()->resolveNode($this->frame, $this->node);
    }

    private function index(): int
    {
        $index = 0;

        /** @var ArgumentExpressionList $parent */
        $parent = $this->node->parent;

        foreach ($parent->getElements() as $element) {
            if ($element === $this->node) {
                return $index;
            }
            $index ++;
        }

        throw new RuntimeException(
            'Could not find myself in the list of my parents children'
        );
    }
}
