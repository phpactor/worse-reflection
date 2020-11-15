<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;
use Microsoft\PhpParser\Node\Expression\BinaryExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\CloneExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Node\Expression\SubscriptExpression;
use Microsoft\PhpParser\Node\Expression\Variable as ParserVariable;
use Microsoft\PhpParser\Node\NumericLiteral;
use Microsoft\PhpParser\Node\Parameter;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\ReservedWord;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\FunctionDeclaration;
use Microsoft\PhpParser\Node\StringLiteral;
use Microsoft\PhpParser\Node\UseVariableName;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\TokenKind;
use Phpactor\WorseReflection\Core\Exception\CouldNotResolveNode;
use Phpactor\WorseReflection\Core\Exception\ItemNotFound;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\Type;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\TernaryExpression;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\ClassLike;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\ConstElement;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionScope;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\NamespacedNameInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Microsoft\PhpParser\Node\Expression\AnonymousFunctionCreationExpression;
use Microsoft\PhpParser\Node\Expression\ParenthesizedExpression;
use Psr\Log\LoggerInterface;

/**
 * @TODO: This class requires SERIOUS refactoring.
 */
class SymbolContextResolver
{
    /**
     * @var MemberTypeResolver
     */
    private $memberTypeResolver;

    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SymbolFactory
     */
    private $symbolFactory;

    /**
     * @var FullyQualifiedNameResolver
     */
    private $nameResolver;

    /**
     * @var ExpressionEvaluator
     */
    private $expressionEvaluator;

    public function __construct(
        Reflector $reflector,
        LoggerInterface $logger,
        SymbolFactory $symbolFactory = null
    ) {
        $this->logger = $logger;
        $this->symbolFactory = $symbolFactory ?: new SymbolFactory();
        $this->memberTypeResolver = new MemberTypeResolver($reflector);
        $this->nameResolver = new FullyQualifiedNameResolver($logger);
        $this->reflector = $reflector;
        $this->expressionEvaluator = new ExpressionEvaluator();
    }

    public function resolveNode(Frame $frame, $node): SymbolContext
    {
        try {
            return $this->_resolveNode($frame, $node);
        } catch (CouldNotResolveNode $couldNotResolveNode) {
            return SymbolContext::none()
                ->withIssue($couldNotResolveNode->getMessage());
        }
    }

    /**
     * Internal interface
     */
    public function _resolveNode(Frame $frame, $node): SymbolContext
    {
        if (false === $node instanceof Node) {
            throw new CouldNotResolveNode(sprintf('Non-node class passed to resolveNode, got "%s"', get_class($node)));
        }

        $context = $this->__resolveNode($frame, $node);
        $context = $context->withScope(new ReflectionScope($node));

        return $context;
    }

    private function __resolveNode(Frame $frame, Node $node): SymbolContext
    {
        $this->logger->debug(sprintf('Resolving: %s', get_class($node)));

        /** @var QualifiedName $node */
        if ($node instanceof QualifiedName) {
            return $this->resolveQualfiedName($frame, $node);
        }

        /** @var ConstElement $node */
        if ($node instanceof ConstElement) {
            return $this->symbolFactory->context(
                $node->getName(),
                $node->getStart(),
                $node->getEndPosition(),
                [
                    'symbol_type' => Symbol::CONSTANT,
                    'container_type' => $this->classTypeFromNode($node)
                ]
            );
        }

        if ($node instanceof Parameter) {
            return $this->resolveParameter($frame, $node);
        }

        if ($node instanceof UseVariableName) {
            $name = $node->getText();
            return $this->resolveVariableName($name, $node, $frame);
        }

        if ($node instanceof ParserVariable) {
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

        if ($node instanceof ParenthesizedExpression) {
            return $this->resolveParenthesizedExpression($frame, $node);
        }

        if ($node instanceof BinaryExpression) {
            $value = $this->expressionEvaluator->evaluate($node);
            return $this->symbolFactory->context(
                $node->getText(),
                $node->getEndPosition(),
                $node->getStart(),
                [
                    'symbol_type' => Symbol::CLASS_,
                    'type' => Type::fromValue($value),
                    'value' => $value,
                ]
            );
        }

        if ($node instanceof ClassDeclaration || $node instanceof TraitDeclaration || $node instanceof InterfaceDeclaration) {
            return $this->symbolFactory->context(
                $node->name->getText($node->getFileContents()),
                $node->name->getEndPosition(),
                $node->name->getStartPosition(),
                [
                    'name' => Name::fromString($node->getNamespacedName()),
                    'symbol_type' => Symbol::CLASS_,
                    'type' => Type::fromString($node->getNamespacedName())
                ]
            );
        }

        if ($node instanceof FunctionDeclaration) {
            return $this->symbolFactory->context(
                $node->name->getText($node->getFileContents()),
                $node->name->getEndPosition(),
                $node->name->getStartPosition(),
                [
                    'name' => Name::fromString($node->getNamespacedName()),
                    'symbol_type' => Symbol::FUNCTION,
                ]
            );
        }

        if ($node instanceof ObjectCreationExpression) {
            return $this->resolveObjectCreationExpression($frame, $node);
        }

        if ($node instanceof SubscriptExpression) {
            $variableValue = $this->_resolveNode($frame, $node->postfixExpression);
            return $this->resolveSubscriptExpression($frame, $variableValue, $node);
        }

        /** @var StringLiteral $node */
        if ($node instanceof StringLiteral) {
            return $this->symbolFactory->context(
                (string) $node->getStringContentsText(),
                $node->getStart(),
                $node->getEndPosition(),
                [
                    'symbol_type' => Symbol::STRING,
                    'type' => Type::string(),
                    'value' => (string) $node->getStringContentsText(),
                    'container_type' => $this->classTypeFromNode($node)
                ]
            );
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

        if ($node instanceof TernaryExpression) {
            return $this->resolveTernaryExpression($frame, $node);
        }

        if ($node instanceof MethodDeclaration) {
            return $this->resolveMethodDeclaration($frame, $node);
        }

        if ($node instanceof CloneExpression) {
            return $this->resolveCloneExpression($frame, $node);
        }

        throw new CouldNotResolveNode(sprintf(
            'Did not know how to resolve node of type "%s" with text "%s"',
            get_class($node),
            $node->getText()
        ));
    }

    private function resolveVariable(Frame $frame, ParserVariable $node)
    {
        if ($node->getFirstAncestor(PropertyDeclaration::class)) {
            return $this->resolvePropertyVariable($node);
        }

        $name = $node->getText();
        return $this->resolveVariableName($name, $node, $frame);
    }

    private function resolvePropertyVariable(ParserVariable $node)
    {
        $info = $this->symbolFactory->context(
            $node->getName(),
            $node->getStart(),
            $node->getEndPosition(),
            [
                'symbol_type' => Symbol::PROPERTY,
            ]
        );

        return $this->memberTypeResolver->propertyType(
            $this->classTypeFromNode($node),
            $info,
            $info->symbol()->name()
        );
    }

    private function resolveMemberAccessExpression(Frame $frame, MemberAccessExpression $node): SymbolContext
    {
        $class = $this->_resolveNode($frame, $node->dereferencableExpression);

        return $this->_infoFromMemberAccess($frame, $class->type(), $node);
    }

    private function resolveCallExpression(Frame $frame, CallExpression $node): SymbolContext
    {
        $resolvableNode = $node->callableExpression;
        return $this->_resolveNode($frame, $resolvableNode);
    }

    private function resolveQualfiedName(Frame $frame, QualifiedName $node)
    {
        if ($node->parent instanceof CallExpression) {
            $name = $node->getResolvedName() ?: $node;
            $name = Name::fromString((string) $name);
            $context = $this->symbolFactory->context(
                $name->short(),
                $node->getStart(),
                $node->getEndPosition(),
                [
                    'symbol_type' => Symbol::FUNCTION,
                ]
            );

            try {
                $function = $this->reflector->reflectFunction($name);
            } catch (NotFound $exception) {
                return $context->withIssue($exception->getMessage());
            }

            return $context->withTypes($function->inferredTypes())
                ->withName($name);
        }

        return $this->symbolFactory->context(
            $node->getText(),
            $node->getStart(),
            $node->getEndPosition(),
            [
                'type' => $this->nameResolver->resolve($node),
                'symbol_type' => Symbol::CLASS_,
                'name' => Name::fromString((string) $node->getResolvedName()),
            ]
        );
    }

    private function resolveParameter(Frame $frame, Parameter $node): SymbolContext
    {
        /** @var MethodDeclaration $method */
        $method = $node->getFirstAncestor(AnonymousFunctionCreationExpression::class, MethodDeclaration::class);

        if ($method instanceof MethodDeclaration) {
            return $this->resolveParameterFromReflection($frame, $method, $node);
        }

        $typeDeclaration = $node->typeDeclaration;
        $type = Type::unknown();

        if ($typeDeclaration instanceof QualifiedName) {
            $type = $this->nameResolver->resolve($node->typeDeclaration);
        }

        if ($typeDeclaration instanceof Token) {
            $type = Type::fromString($typeDeclaration->getText($node->getFileContents()));
        }

        $value = null;
        if ($node->default) {
            $value = $this->_resolveNode($frame, $node->default)->value();
        }

        return $this->symbolFactory->context(
            $node->variableName->getText($node->getFileContents()),
            $node->variableName->getStartPosition(),
            $node->variableName->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
                'type' => $type,
                'value' => $value,
            ]
        );
    }

    private function resolveParameterFromReflection(Frame $frame, MethodDeclaration $method, Parameter $node): SymbolContext
    {
        $class = $this->getClassLikeAncestor($node);

        if (null === $class) {
            throw new CouldNotResolveNode(sprintf(
                'Cannot find class context "%s" for parameter',
                $node->getName()
            ));
        }

        /** @var ReflectionClass|ReflectionInterface $reflectionClass */
        $reflectionClass = $this->reflector->reflectClassLike($class->getNamespacedName()->__toString());

        try {
            $reflectionMethod = $reflectionClass->methods()->get($method->getName());
        } catch (ItemNotFound $notFound) {
            throw new CouldNotResolveNode(sprintf(
                'Could not find method "%s" in class "%s"',
                $method->getName(),
                $reflectionClass->name()->__toString()
            ), 0, $notFound);
        }

        if (null === $node->getName()) {
            throw new CouldNotResolveNode(
                'Node name for parameter resolved to NULL'
            );
        }

        if (!$reflectionMethod->parameters()->has($node->getName())) {
            throw new CouldNotResolveNode(sprintf(
                'Cannot find parameter "%s" for method "%s" in class "%s"',
                $node->getName(),
                $reflectionMethod->name(),
                $reflectionClass->name()
            ));
        }

        $reflectionParameter = $reflectionMethod->parameters()->get($node->getName());

        return $this->symbolFactory->context(
            $node->variableName->getText($node->getFileContents()),
            $node->variableName->getStartPosition(),
            $node->variableName->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
                'type' => $reflectionParameter->inferredTypes()->best(),
                'value' => $reflectionParameter->default()->value(),
            ]
        );
    }

    private function resolveNumericLiteral(NumericLiteral $node): SymbolContext
    {
        // note hack to cast to either an int or a float
        $value = $node->getText() + 0;

        return $this->symbolFactory->context(
            $node->getText(),
            $node->getStart(),
            $node->getEndPosition(),
            [
                'symbol_type' => Symbol::NUMBER,
                'type' => is_float($value) ? Type::float() : Type::int(),
                'value' => $value,
                'container_type' => $this->classTypeFromNode($node)
            ]
        );
    }

    private function resolveReservedWord(Node $node): SymbolContext
    {
        $symbolType = $containerType = $type = $value = null;
        $word = strtolower($node->getText());

        if ('null' === $word) {
            $type = Type::null();
            $symbolType = Symbol::BOOLEAN;
            $containerType = $this->classTypeFromNode($node);
        }

        if ('false' === $word) {
            $value = false;
            $type = Type::bool();
            $symbolType = Symbol::BOOLEAN;
            $containerType = $this->classTypeFromNode($node);
        }

        if ('true' === $word) {
            $type = Type::bool();
            $value = true;
            $symbolType = Symbol::BOOLEAN;
            $containerType = $this->classTypeFromNode($node);
        }

        $info = $this->symbolFactory->context(
            $node->getText(),
            $node->getStart(),
            $node->getEndPosition(),
            [
                'value' => $value,
                'type' => $type,
                'symbol_type' => $symbolType === null ? Symbol::UNKNOWN : $symbolType,
                'container_type' => $containerType,
            ]
        );

        if (null === $symbolType) {
            $info = $info->withIssue(sprintf('Could not resolve reserved word "%s"', $node->getText()));
        }

        if (null === $type) {
            $info = $info->withIssue(sprintf('Could not resolve reserved word "%s"', $node->getText()));
        }

        return $info;
    }

    private function resolveArrayCreationExpression(Frame $frame, ArrayCreationExpression $node): SymbolContext
    {
        $array  = [];

        if (null === $node->arrayElements) {
            return $this->symbolFactory->context(
                $node->getText(),
                $node->getStart(),
                $node->getEndPosition(),
                [
                    'type' => Type::array(),
                    'value' => []
                ]
            );
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

        return $this->symbolFactory->context(
            $node->getText(),
            $node->getStart(),
            $node->getEndPosition(),
            [
                'type' => Type::array(),
                'value' => $array
            ]
        );
    }

    private function resolveSubscriptExpression(
        Frame $frame,
        SymbolContext $info,
        SubscriptExpression $node = null
    ): SymbolContext {
        if (null === $node->accessExpression) {
            $info = $info->withIssue(sprintf(
                'Subscript expression "%s" is incomplete',
                (string) $node->getText()
            ));
            return $info;
        }

        $node = $node->accessExpression;

        if ($info->type() != Type::array()) {
            $info = $info->withIssue(sprintf(
                'Not resolving subscript expression of type "%s"',
                (string) $info->type()
            ));
            return $info;
        }

        $subjectValue = $info->value();

        if (false === is_array($subjectValue)) {
            $info = $info->withIssue(sprintf(
                'Array value for symbol "%s" is not an array, is a "%s"',
                (string) $info->symbol(),
                gettype($subjectValue)
            ));

            return $info;
        }

        if ($node instanceof StringLiteral) {
            $string = $this->_resolveNode($frame, $node);

            if (array_key_exists($string->value(), $subjectValue)) {
                $value = $subjectValue[$string->value()];
                return $string->withValue($value);
            }
        }

        $info = $info->withIssue(sprintf(
            'Did not resolve access expression for node type "%s"',
            get_class($node)
        ));

        return $info;
    }

    private function resolveScopedPropertyAccessExpression(Frame $frame, ScopedPropertyAccessExpression $node): SymbolContext
    {
        $name = $node->scopeResolutionQualifier->getText();
        $parent = $this->nameResolver->resolve($node, $name);

        return $this->_infoFromMemberAccess($frame, $parent, $node);
    }

    private function resolveObjectCreationExpression(Frame $frame, $node): SymbolContext
    {
        if (false === $node->classTypeDesignator instanceof Node) {
            throw new CouldNotResolveNode(sprintf('Could not create object from "%s"', get_class($node)));
        }

        return $this->_resolveNode($frame, $node->classTypeDesignator);
    }

    private function resolveTernaryExpression(Frame $frame, TernaryExpression $node): SymbolContext
    {
        // assume true
        if ($node->ifExpression) {
            $ifValue = $this->_resolveNode($frame, $node->ifExpression);

            if ($ifValue->type()->isDefined()) {
                return $ifValue;
            }
        }

        // if expression was not defined, fallback to condition
        $conditionValue = $this->_resolveNode($frame, $node->condition);

        if ($conditionValue->type()->isDefined()) {
            return $conditionValue;
        }

        return SymbolContext::none();
    }

    private function resolveMethodDeclaration(Frame $frame, MethodDeclaration $node): SymbolContext
    {
        $classNode = $this->getClassLikeAncestor($node);
        $classSymbolContext = $this->_resolveNode($frame, $classNode);

        return $this->symbolFactory->context(
            $node->name->getText($node->getFileContents()),
            $node->name->getStartPosition(),
            $node->name->getEndPosition(),
            [
                'container_type' => $classSymbolContext->type(),
                'symbol_type' => Symbol::METHOD,
            ]
        );
    }

    private function _infoFromMemberAccess(Frame $frame, Type $classType, Node $node): SymbolContext
    {
        assert($node instanceof MemberAccessExpression || $node instanceof ScopedPropertyAccessExpression);

        $memberName = $node->memberName->getText($node->getFileContents());
        $memberType = $node->getParent() instanceof CallExpression ? 'method' : 'property';

        if ($node->memberName instanceof Node) {
            $memberNameInfo = $this->_resolveNode($frame, $node->memberName);
            if (is_string($memberNameInfo->value())) {
                $memberName = $memberNameInfo->value();
            }
        }

        if (
            'property' === $memberType
            && $node instanceof ScopedPropertyAccessExpression
            && is_string($memberName)
            && substr($memberName, 0, 1) !== '$'
        ) {
            $memberType = 'constant';
        }

        $information = $this->symbolFactory->context(
            $memberName,
            $node->getStart(),
            $node->getEndPosition(),
            [
                'symbol_type' => $memberType,
            ]
        );

        // if the classType is a call expression, then this is a method call
        /** @var SymbolContext $info */
        $info = $this->memberTypeResolver->{$memberType . 'Type'}($classType, $information, $memberName);

        if ('property' === $memberType) {
            $frameTypes = $this->getFrameTypesForPropertyAtPosition(
                $frame,
                (string) $memberName,
                $classType,
                $node->getEndPosition(),
            );

            foreach ($frameTypes as $types) {
                $info = $info->withTypes(
                    $info->types()->merge($types),
                );
            }
        }

        $this->logger->debug(sprintf(
            'Resolved type "%s" for %s "%s" of class "%s"',
            (string) $info->type(),
            $memberType,
            $memberName,
            (string) $classType
        ));

        return $info;
    }

    private function classTypeFromNode(Node $node)
    {
        $classNode = $this->getClassLikeAncestor($node);

        if (null === $classNode) {
            // TODO: Wrning here
            return;
        }

        assert($classNode instanceof NamespacedNameInterface);

        return Type::fromString($classNode->getNamespacedName());
    }

    private function resolveParenthesizedExpression(Frame $frame, ParenthesizedExpression $node)
    {
        return $this->__resolveNode($frame, $node->expression);
    }

    private function resolveVariableName(string $name, Node $node, Frame $frame)
    {
        $varName = ltrim($name, '$');
        $offset = $node->getEndPosition();
        $variables = $frame->locals()->lessThanOrEqualTo($offset)->byName($varName);

        if (0 === $variables->count()) {
            return $this->symbolFactory->context(
                $name,
                $node->getStart(),
                $node->getEndPosition(),
                [
                    'symbol_type' => Symbol::VARIABLE
                ]
            )->withIssue(sprintf('Variable "%s" is undefined', $varName));
        }

        return $variables->last()->symbolContext();
    }

    /**
     * @return ClassDeclaration|TraitDeclaration|InterfaceDeclaration
     */
    private function getClassLikeAncestor(Node $node)
    {
        $ancestor = $node->getFirstAncestor(ObjectCreationExpression::class, ClassLike::class);

        if ($ancestor instanceof ObjectCreationExpression) {
            if ($ancestor->classTypeDesignator instanceof Token) {
                if ($ancestor->classTypeDesignator->kind == TokenKind::ClassKeyword) {
                    throw new CouldNotResolveNode('Resolving anonymous classes is not currently supported');
                }
            }

            return $this->getClassLikeAncestor($ancestor);
        }

        return $ancestor;
    }

    private function resolveCloneExpression(Frame $frame, CloneExpression $node): SymbolContext
    {
        return $this->__resolveNode($frame, $node->expression);
    }

    /**
     * @return Generator<int, Types, null, void>
     */
    private function getFrameTypesForPropertyAtPosition(
        Frame $frame,
        string $propertyName,
        Type $classType,
        int $position
    ): Generator {
        $assignments = $frame->properties()
            ->lessThanOrEqualTo($position)
            ->byName($propertyName)
        ;

        /** @var Variable $variable */
        foreach ($assignments as $variable) {
            $symbolContext = $variable->symbolContext();
            $containerType = $symbolContext->containerType();

            if (
                !$containerType
                || !$containerType->isClass()
                || $containerType->className() != $classType->className()
            ) {
                // Ignore if not a class, could throw LogicException since it shoudl not append
                // Or if the symbol is for a different class
                continue;
            }

            yield $symbolContext->types();
        }
    }
}
