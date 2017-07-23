<?php

namespace Phpactor\WorseReflection\Reflection\Inference;

use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Parameter;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Node\Expression\SubscriptExpression;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Type;
use Microsoft\PhpParser\Node\NamespaceUseClause;
use Phpactor\WorseReflection\ClassName;
use Phpactor\WorseReflection\Exception\ClassNotFound;
use Phpactor\WorseReflection\Reflection\ReflectionClass;
use Microsoft\PhpParser\Node\StringLiteral;
use Microsoft\PhpParser\Node\NumericLiteral;
use Phpactor\WorseReflection\Reflection\Inference\Value;
use Microsoft\PhpParser\Node\ReservedWord;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;

class NodeValueResolver
{
    /**
     * @var Reflector $reflector
     */
    private $reflector;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var ValueResolver
     */
    private $valueResolver;

    public function __construct(Reflector $reflector, Logger $logger = null, ValueResolver $valueResolver = null)
    {
        $this->reflector = $reflector;
        $this->logger = $logger ?: new ArrayLogger();
        $this->valueResolver = $valueResolver ?: new ValueResolver();
    }

    public function resolveNode(Frame $frame, Node $node): Value
    {
        if ($node instanceof QualifiedName) {
            return Value::fromType($this->resolveQualifiedName($node));
        }

        if ($node instanceof Parameter) {
            if ($node->typeDeclaration instanceof QualifiedName) {
                return Value::fromType($this->resolveQualifiedName($node->typeDeclaration));
            }
        }

        if ($node instanceof Variable) {
            return $this->resolveVariable($frame, $node->getText());
        }

        if ($node instanceof MemberAccessExpression || $node instanceof CallExpression) {
            return $this->resolveMemberAccess($frame, $node);
        }

        if ($node instanceof ClassDeclaration || $node instanceof InterfaceDeclaration) {
            return Value::fromType(Type::fromString($node->getNamespacedName()));
        }

        if ($node instanceof ObjectCreationExpression) {
            return Value::fromType($this->resolveQualifiedName($node->classTypeDesignator));
        }

        if ($node instanceof SubscriptExpression) {
            return Value::fromType($this->resolveVariable($frame, $node->getText()));
        }

        if ($node instanceof StringLiteral) {
            return Value::fromTypeAndValue(Type::string(), (string) $node->getStringContentsText());
        }

        if ($node instanceof NumericLiteral) {
            $value = $node->getText() + 0;
            // note hack to cast to either an int or a float
            return Value::fromTypeAndValue(is_float($value) ? Type::float() : Type::int(), $value);
        }

        if ($node instanceof ReservedWord) {
            if ('null' === $node->getText()) {
                return Value::fromTypeAndValue(Type::null(), null);
            }

            if ('false' === $node->getText()) {
                return Value::fromTypeAndValue(Type::bool(), false);
            }

            if ('true' === $node->getText()) {
                return Value::fromTypeAndValue(Type::bool(), true);
            }
        }

        if ($node instanceof ArrayCreationExpression) {
            $array  = [];

            if (null === $node->arrayElements) {
                return Value::fromTypeAndValue(Type::array(), []);
            }

            foreach ($node->arrayElements->getElements() as $element) {
                $value = $this->resolveNode($frame, $element->elementValue)->value();
                if ($element->elementKey) {
                    $key = $this->resolveNode($frame, $element->elementKey)->value();
                    $array[$key] = $value;
                    continue;
                }

                $array[] = $value;
            }

            return Value::fromTypeAndValue(Type::array(), $array);
        }

        $this->logger->warning(sprintf(
            'Could not resolve type for node "%s"',
            get_class($node)
        ));

        return Value::none();
    }

    private function resolveVariable(Frame $frame, string $name)
    {
        if (0 === $frame->locals()->byName($name)->count()) {
            return Value::none();
        }

        return $frame->locals()->byName($name)->first()->value();
    }

    private function resolveMemberAccess(Frame $frame, Expression $node, $list = [])
    {
        $ancestors = [];
        while ($node instanceof MemberAccessExpression || $node instanceof CallExpression) {
            if ($node instanceof CallExpression) {
                $node = $node->callableExpression;
                continue;
            }

            $ancestors[] = $node;
            $node = $node->dereferencableExpression;
        }

        $ancestors[] = $node;
        $ancestors = array_reverse($ancestors);

        $parent = null;
        foreach ($ancestors as $ancestor) {
            if ($parent === null) {
                $parent = $this->resolveNode($frame, $ancestor);

                if (Type::unknown() == $parent) {
                    return Type::unknown();
                }

                continue;
            }

            $value = $this->resolveMemberType($parent, $ancestor);
            $parent = $value;
        }

        return $value;
    }

    private function resolveMemberType(Value $parent, $node): Value
    {
        $memberName = $node->memberName->getText($node->getFileContents());

        $type = $this->methodType($parent->type(), $memberName);

        if (Type::unknown() != $type) {
            return Value::fromType($type);
        }

        $type = $this->propertyType($parent->type(), $memberName);

        return Value::fromType($type);
    }

    public function resolveQualifiedName(Node $node, string $name = null): Type
    {
        $name = $name ?: $node->getText();

        if (substr($name, 0, 1) === '\\') {
            return Type::fromString($name);
        }

        if ($name == 'self') {
            $class = $node->getFirstAncestor(ClassDeclaration::class, InterfaceDeclaration::class);

            return Type::fromString($class->getNamespacedName());
        }

        $imports = $node->getImportTablesForCurrentScope();
        $classImports = $imports[0];

        if (isset($classImports[$name])) {
            return Type::fromString((string) $classImports[$name]);
        }

        if ($node->getParent() instanceof NamespaceUseClause) {
            return Type::fromString((string) $name);
        }

        if ($namespaceDefinition = $node->getNamespaceDefinition()) {
            return Type::fromArray([$namespaceDefinition->name->getText(), $name]);
        }

        return Type::fromString($name);
    }

    private function methodType(Type $type, string $name): Type
    {
        $class = null;
        try {
            $class = $this->reflector->reflectClass(ClassName::fromString((string) $type));
        } catch (SourceNotFound $e) {
        } catch (ClassNotFound $e) {
        }

        if (null === $class) {
            $this->logger->warning(sprintf(
                'Unable to locate class "%s" for method "%s"', (string) $type, $name
            ));
            return Type::unknown();
        }

        try {
            $method = $class->methods()->get($name);
        } catch (\InvalidArgumentException $e) {
            return Type::unknown();
        }

        $type = $method->inferredReturnType()->className() ?: (string) $method->inferredReturnType();

        return Type::fromString($type);
    }

    private function propertyType(Type $type, string $name): Type
    {
        $class = null;
        try {
            $class = $this->reflector->reflectClass(ClassName::fromString((string) $type));
        } catch (SourceCodeNotFound $e) {
        } catch (ClassNotFound $e) {
        }

        if (null === $class) {
            $this->logger->warning(sprintf(
                'Unable to locate class "%s" for property "%s"', (string) $type, $name
            ));
            return Type::unknown();
        }

        if (!$class instanceof ReflectionClass) {
            return Type::unknown();
        }

        if (false === $class->properties()->has($name)) {
            $this->logger->warning(sprintf(
                'Class "%s" has no property named "%s"',
                (string) $type, $name
            ));

            return Type::unknown();
        }

        return $class->properties()->get($name)->type();
    }

    private function resolveScalarValue(Expression $expression)
    {

        return null;
    }
}

