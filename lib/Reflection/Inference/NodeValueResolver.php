<?php

namespace Phpactor\WorseReflection\Reflection\Inference;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Node\Expression\SubscriptExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\NamespaceUseClause;
use Microsoft\PhpParser\Node\NumericLiteral;
use Microsoft\PhpParser\Node\Parameter;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\ReservedWord;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node\StringLiteral;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Token;
use Phpactor\WorseReflection\ClassName;
use Phpactor\WorseReflection\Exception\ClassNotFound;
use Phpactor\WorseReflection\Logger;
use Phpactor\WorseReflection\Reflection\Inference\Value;
use Phpactor\WorseReflection\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Type;
use Phpactor\WorseReflection\Logger\ArrayLogger;
use Phpactor\WorseReflection\Exception\SourceNotFound;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;

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

    public function __construct(Reflector $reflector, Logger $logger)
    {
        $this->reflector = $reflector;
        $this->logger = $logger;
    }

    public function resolveNode(Frame $frame, Node $node): Value
    {
        // traverse to the root expression.
        if ($node instanceof MemberAccessExpression) {
            while ($node->getParent() instanceof Expression) {
                $node = $node->getParent();
            }
        }

        // jump to the container for SubscriptExpression (array access)
        if ($node->getParent() instanceof SubscriptExpression) {
            return $this->resolveNode($frame, $node->getParent());
        }

        return $this->_resolveNode($frame, $node);
    }

    private function _resolveNode(Frame $frame, Node $node)
    {
        $this->logger->debug(sprintf('Resolving: %s', get_class($node)));
        if ($node instanceof QualifiedName) {
            return Value::fromType($this->resolveQualifiedName($node));
        }

        if ($node instanceof Parameter) {
            return $this->resolveParameter($frame, $node);
        }

        if ($node instanceof Variable) {
            return $this->resolveVariable($frame, $node);
        }

        if ($node instanceof MemberAccessExpression) {
            return $this->resolveMemberAccessExpression($frame, $node);
        }

        if ($node instanceof CallExpression) {
            return $this->resolveCallExpression($frame, $node);
        }

        if ($node instanceof ScopedPropertyAccessExpression) {
            return $this->resolveScopedPropertyAccessExpression($frame, $node);
        }

        if ($node instanceof ClassDeclaration || $node instanceof InterfaceDeclaration) {
            return Value::fromType(Type::fromString($node->getNamespacedName()));
        }

        if ($node instanceof ObjectCreationExpression) {
            return Value::fromType($this->resolveQualifiedName($node->classTypeDesignator));
        }

        if ($node instanceof SubscriptExpression) {
            $variableValue = $this->_resolveNode($frame, $node->postfixExpression);
            return $this->resolveAccessExpression($frame, $variableValue, $node->accessExpression);
        }

        if ($node instanceof StringLiteral) {
            return Value::fromTypeAndValue(Type::string(), (string) $node->getStringContentsText());
        }

        if ($node instanceof NumericLiteral) {
            return $this->resolveNumericLiteral($node);
        }

        if ($node instanceof ReservedWord) {
            return $this->resolveReservedWord($node);
        }

        if ($node instanceof ArrayCreationExpression) {
            return $this->resolveArrayCreationExpression($frame, $node);
        }

        if ($node instanceof ArgumentExpression) {
            return $this->_resolveNode($frame, $node->expression);
        }

        $this->logger->warning(sprintf(
            'Did not know how to resolve node of type "%s" with text "%s"', get_class($node), $node->getText()
        ));

        return Value::none();
    }

    private function resolveVariable(Frame $frame, Variable $node)
    {
        $name = $node->getText();
        $offset = $node->getFullStart();
        $variables = $frame->locals()->lessThanOrEqualTo($offset)->byName($name);

        if (0 === $variables->count()) {
            return Value::none();
        }

        return $variables->first()->value();
    }

    private function resolveMemberAccessExpression(Frame $frame, MemberAccessExpression $node): Value
    {

        $parent = $this->_resolveNode($frame, $node->dereferencableExpression);

        return $this->_typeFromMemberAccess($parent->type(), $node);
    }

    private function resolveCallExpression(Frame $frame, CallExpression $node): Value
    {
        $resolvableNode = $node->callableExpression;
        return $this->_resolveNode($frame, $resolvableNode);
    }

    private function resolveMemberType(Value $parent, Node $node): Value
    {
        $memberNode = $node instanceof CallExpression ? $node->callableExpression : $node;
        $memberName = $memberNode->memberName->getText($node->getFileContents());

        if ($node instanceof MemberAccessExpression) {
            $type = $this->propertyType($parent->type(), $memberName);

            return Value::fromType($type);
        }

        return Value::fromType($this->methodType($parent->type(), $memberName));

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
            // TODO: Test this here!!
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

        return $method->inferredReturnType();
    }

    private function propertyType(Type $type, string $name): Type
    {
        $class = null;
        try {
            $class = $this->reflector->reflectClass(ClassName::fromString((string) $type));
        } catch (SourceNotFound $e) {
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

            if ($class->constants()->has($name)) {
                $this->logger->warning(sprintf(
                    'Do not know how to resolve types of constants',
                    (string) $type, $name
                ));
                return Type::unknown();
            }

            return Type::unknown();
        }

        return $class->properties()->get($name)->type();
    }

    private function resolveParameter(Frame $frame, Node $node)
    {
        $typeDeclaration = $node->typeDeclaration;
        $type = Type::unknown();

        if ($typeDeclaration instanceof QualifiedName) {
            $type = $this->resolveQualifiedName($node->typeDeclaration);
        }
        
        if ($typeDeclaration instanceof Token) {
            $type = Type::fromString($typeDeclaration->getText($node->getFileContents()));
        }

        if ($node->default) {
            $value = $this->_resolveNode($frame, $node->default);
            return Value::fromTypeAndValue($type, $value->value());
        }

        return Value::fromType($type);
    }

    private function resolveNumericLiteral(Node $node)
    {
        // note hack to cast to either an int or a float
        $value = $node->getText() + 0;

        return Value::fromTypeAndValue(is_float($value) ? Type::float() : Type::int(), $value);
    }

    private function resolveReservedWord(Node $node)
    {
        if ('null' === $node->getText()) {
            return Value::fromTypeAndValue(Type::null(), null);
        }

        if ('false' === $node->getText()) {
            return Value::fromTypeAndValue(Type::bool(), false);
        }

        if ('true' === $node->getText()) {
            return Value::fromTypeAndValue(Type::bool(), true);
        }

        $this->logger->warning(sprintf('Could not resolve reserved word "%s"', $node->getText()));

        // TODO: Not tested
        return Value::none();
    }

    private function resolveArrayCreationExpression(Frame $frame, Node $node)
    {
        $array  = [];

        if (null === $node->arrayElements) {
            return Value::fromTypeAndValue(Type::array(), []);
        }

        foreach ($node->arrayElements->getElements() as $element) {
            $value = $this->_resolveNode($frame, $element->elementValue)->value();
            if ($element->elementKey) {
                $key = $this->_resolveNode($frame, $element->elementKey)->value();
                $array[$key] = $value;
                continue;
            }

            $array[] = $value;
        }

        return Value::fromTypeAndValue(Type::array(), $array);
    }

    private function resolveAccessExpression(Frame $frame, Value $subject, Node $node): Value
    {
        // TODO: test me
        if ($subject->value() == Value::none()) {
            return Value::none();
        }

        if ($subject->type() != Type::array()) {
            $this->logger->warning(sprintf(
                'Not resolving access expression of type "%s"',
                (string) $subject->type()
            ));
            return Value::none();
        }

        $subjectValue = $subject->value();

        if ($node instanceof StringLiteral) {
            $string = $this->_resolveNode($frame, $node);

            if (array_key_exists($string->value(), $subjectValue)) {
                $value = $subjectValue[$string->value()];
                return Value::fromTypeAndValue(Type::fromValue($value), $value);
            }
        }

        $this->logger->warning(sprintf(
            'Did not resolve access expression for node type "%s"', get_class($node)
        ));

        return Value::none();
    }

    private function resolveScopedPropertyAccessExpression(Frame $frame, ScopedPropertyAccessExpression $node)
    {
        $parent = $this->resolveQualifiedName($node, $node->scopeResolutionQualifier->getText());

        return $this->_typeFromMemberAccess($parent, $node);
    }

    private function _typeFromMemberAccess(Type $parent, Node $node)
    {
        $memberType = $node->getParent() instanceof CallExpression ? 'method' : 'property';
        $memberName = $node->memberName->getText($node->getFileContents());

        // if the parent is a call expression, then this is a method call
        $type = $this->{$memberType . 'Type'}($parent, $memberName);

        $this->logger->debug(sprintf(
            'Resolved type "%s" for %s "%s" of class "%s"',
            (string) $type,
            $memberType,
            $memberName,
            (string) $parent
        ));

        return Value::fromType($type);
    }
}
