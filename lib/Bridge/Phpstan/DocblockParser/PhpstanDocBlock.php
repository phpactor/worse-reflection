<?php

namespace Phpactor\WorseReflection\Bridge\Phpstan\DocblockParser;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockVars;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockVar;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
use Phpactor\WorseReflection\Core\Type;

class PhpstanDocBlock implements DocBlock
{
    /**
     * @var PhpDocNode
     */
    private $node;

    /**
     * @var string
     */
    private $raw;

    public function __construct(string $raw, PhpDocNode $node)
    {
        $this->node = $node;
        $this->raw = $raw;
    }

    public function isDefined(): bool
    {
        return trim($this->raw) != '';
    }

    public function raw(): string
    {
        return $this->raw;
    }

    public function formatted(): string
    {
        return implode(PHP_EOL, $this->node->children);
    }

    public function returnTypes(): array
    {
        $types = $this->node->getReturnTagValues();
        return array_map(function (ReturnTagValueNode $node) {
            return Type::fromString((string) $node->type);
        }, $types);
    }

    public function methodTypes(string $methodName): array
    {
        $methodValues = $this->node->getMethodTagValues();

        $types = array_filter($methodValues, function (MethodTagValueNode $node) use ($methodName) {
            return $methodName == $node->methodName;
        });

        return array_map(function (MethodTagValueNode $node) {
            return Type::fromString((string) $node->returnType);
        }, $types);
    }

    public function vars(): DocBlockVars
    {
        $types = $this->node->getVarTagValues();
        return new DocBlockVars(array_map(function (VarTagValueNode $node) {
            return DocBlockVar::fromVarNameAndType($node->variableName, (string) $node->type);
        }, $types));
    }

    public function inherits(): bool
    {
        return false !== stripos($this->raw, '{inheritDoc}');
    }
}
