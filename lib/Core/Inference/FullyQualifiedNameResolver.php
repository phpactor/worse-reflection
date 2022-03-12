<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Type;
use Microsoft\PhpParser\ClassLike;
use Microsoft\PhpParser\Node\NamespaceUseClause;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\NamespacedNameInterface;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\IterableType;
use Phpactor\WorseReflection\Core\Type\SelfType;
use Phpactor\WorseReflection\Core\Type\StaticType;
use Psr\Log\LoggerInterface;

class FullyQualifiedNameResolver
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function resolve(Node $node, $type = null, Name $currentClass = null): Type
    {
        $type = $type ?: $node->getText();

        /** @var Type $type */
        $type = $type instanceof Type ? $type : TypeFactory::fromString($type);

        if ($type instanceof IterableType) {
            // TODO: Here
            $arrayType = $this->resolve($node, $type->arrayType());

            $type = $type->withArrayType($arrayType);
        }

        if ($this->isFunctionCall($node)) {
            return Type::unknown();
        }
        
        if ($this->isUseDefinition($node)) {
            return TypeFactory::fromString((string) $type);
        }

        if ($type instanceof ScalarType) {
            return $type;
        }

        if ($type instanceof ClassType && $type->name->wasFullyQualified()) {
            return $type;
        }

        if ($type instanceof SelfType || $type instanceof StaticType) {
            return $this->currentClass($node, $currentClass);
        }

        // TODO: how is this a thing?
        // if ((string) $type == 'parent') {
        //     return $this->parentClass($node);
        // }

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


        return TypeFactory::fromString($class->classBaseClause->baseClass->getResolvedName());
    }

    private function currentClass(Node $node, Name $currentClass = null)
    {
        if ($currentClass) {
            return TypeFactory::fromString($currentClass->full());
        }
        $class = $node->getFirstAncestor(ClassLike::class);

        if (null === $class) {
            return Type::unknown();
        }

        assert($class instanceof NamespacedNameInterface);

        return TypeFactory::fromString($class->getNamespacedName());
    }

    private function isUseDefinition(Node $node)
    {
        return $node->getParent() instanceof NamespaceUseClause;
    }

    private function fromClassImports(Node $node, Type $type): ?Type
    {
        $imports = $node->getImportTablesForCurrentScope();
        $classImports = $imports[0];

        if (!$type instanceof ClassType) {
            return $type;
        }

        $className = $type->name->__toString();

        if (isset($classImports[$className])) {
            return $type->withClassName((string) $classImports[$className]);
        }

        if (isset($classImports[$type->name->head()->__toString()])) {
            // namespace was imported
            return $type->withClassName((string) $classImports[(string) $className->head()] . '\\' . (string) $className->tail());
        }

        return null;
    }
}
