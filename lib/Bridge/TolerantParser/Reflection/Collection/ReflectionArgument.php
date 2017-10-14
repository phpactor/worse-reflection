<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection;

use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Phpactor\WorseReflection\Core\Position;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Microsoft\PhpParser\Node\Expression\Variable;
use Phpactor\WorseReflection\Core\Inference\SymbolInformation;
use Phpactor\WorseReflection\Core\Reflection\ReflectionArgument as CoreReflectionArgument;

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

            if (substr($name, 0, 1) == '$') {
                return substr($name, 1);
            }

            return $name;
        }

        if ($this->type()->isDefined()) {
            return lcfirst((string) $this->type());
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
            $this->node->getFullStart(),
            $this->node->getStart(),
            $this->node->getEndPosition()
        );
    }

    private function info(): SymbolInformation
    {
        return $this->services->symbolInformationResolver()->resolveNode($this->frame, $this->node);
    }

    private function index(): int
    {
        $index = 0;
        foreach ($this->node->parent->getElements() as $element) {
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
