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

class FullyQualifiedNameResolver
{
    /**
     * @var Logger
     */
    private $logger;

    public function __construct(
        Logger $logger
    )
    {
        $this->logger = $logger;
    }

    public function resolve(Node $node, string $name = null): Type
    {
        $name = $name ?: $node->getText();

        if (!$node instanceof ScopedPropertyAccessExpression && $node->parent instanceof CallExpression) {
            return Type::unknown();
        }
        
        $type = Type::fromString($name);

        if (substr($name, 0, 1) === '\\') {
            return $type;
        }

        if (in_array($name, ['self', 'static'])) {
            $class = $node->getFirstAncestor(ClassLike::class);

            return Type::fromString($class->getNamespacedName());
        }

        if ($name == 'parent') {
            /** @var $class ClassDeclaration */
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

        $imports = $node->getImportTablesForCurrentScope();
        $classImports = $imports[0];

        if ($type->isPrimitive()) {
            return $type;
        }

        $className = $type->className();

        if (isset($classImports[$name])) {
            // class was imported
            return Type::fromString((string) $classImports[$name]);
        }

        if (isset($classImports[(string) $className->head()])) {
            // namespace was imported
            return Type::fromString((string) $classImports[(string) $className->head()] . '\\' . (string) $className->tail());
        }

        if ($node->getParent() instanceof NamespaceUseClause) {
            return Type::fromString((string) $name);
        }

        if ($namespaceDefinition = $node->getNamespaceDefinition()) {
            return Type::fromArray([$namespaceDefinition->name->getText(), $name]);
        }

        return Type::fromString($name);
    }
}
