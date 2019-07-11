<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Phpactor\WorseReflection\Core\Type;
use Microsoft\PhpParser\ClassLike;
use Phpactor\WorseReflection\Core\Logger;
use Microsoft\PhpParser\Node\NamespaceUseClause;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\NamespacedNameInterface;
use Phpactor\WorseReflection\Core\Name;

class FullyQualifiedNameResolver
{
    /**
     * @var Logger
     */
    private $logger;

    public function __construct(
        Logger $logger
    ) {
        $this->logger = $logger;
    }

    public function resolve(Node $node, $type = null, Name $currentClass = null): Type
    {
        $type = $type ?: $node->getText();

        /** @var Type $type */
        $type = $type instanceof Type ? $type : Type::fromString($type);

        if ($type->arrayType()->isDefined()) {
            $arrayType = $this->resolve($node, $type->arrayType());

            $type = $type->withArrayType($arrayType);
        }

        if ($this->isFunctionCall($node)) {
            return Type::unknown();
        }
        
        if ($this->isUseDefinition($node)) {
            return Type::fromString((string) $type);
        }

        if ($type->isPrimitive()) {
            return $type;
        }

        if ($type->className()->wasFullyQualified()) {
            return $type;
        }

        if (in_array((string) $type, ['self', 'static', '$this'])) {
            return $this->currentClass($node, $currentClass);
        }

        if ((string) $type == 'parent') {
            return $this->parentClass($node);
        }

        if ($importedType = $this->fromClassImports($node, $type)) {
            return $importedType;
        }

        $namespaceDefinition = $node->getNamespaceDefinition();
        if ($namespaceDefinition && $namespaceDefinition->name instanceof QualifiedName) {
            $className = $type->className()->prepend($namespaceDefinition->name->getText());
            return $type->withClassName($className);
        }

        return $type;
    }

    private function isFunctionCall(Node $node)
    {
        return false === $node instanceof ScopedPropertyAccessExpression &&
            $node->parent instanceof CallExpression;
    }

    private function isFullyQualified(string $name)
    {
        return substr($name, 0, 1) === '\\';
    }

    private function parentClass(Node $node)
    {
        /** @var ClassDeclaration $class */
        $class = $node->getFirstAncestor(ClassDeclaration::class);

        if (null === $class) {
            $this->logger->warning('"parent" keyword used outside of class scope');
            return Type::unknown();
        }

        if (null === $class->classBaseClause) {
            $this->logger->warning('"parent" keyword used but class does not extend anything');
            return Type::unknown();
        }


        return Type::fromString($class->classBaseClause->baseClass->getResolvedName());
    }

    private function currentClass(Node $node, Name $currentClass = null)
    {
        if ($currentClass) {
            return Type::fromString($currentClass->full());
        }
        $class = $node->getFirstAncestor(ClassLike::class);

        if (null === $class) {
            return Type::unknown();
        }

        assert($class instanceof NamespacedNameInterface);

        return Type::fromString($class->getNamespacedName());
    }

    private function isUseDefinition(Node $node)
    {
        return $node->getParent() instanceof NamespaceUseClause;
    }

    private function fromClassImports(Node $node, Type $type)
    {
        $imports = $node->getImportTablesForCurrentScope();
        $classImports = $imports[0];
        $className = $type->className();

        if (isset($classImports[(string) $type])) {
            return $type->withClassName((string) $classImports[(string) $type]);
        }

        if (isset($classImports[(string) $className->head()])) {
            // namespace was imported
            return $type->withClassName((string) $classImports[(string) $className->head()] . '\\' . (string) $className->tail());
        }
    }
}
