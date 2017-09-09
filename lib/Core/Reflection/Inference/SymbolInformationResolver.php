<?php

namespace Phpactor\WorseReflection\Core\Reflection\Inference;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Node\Expression\SubscriptExpression;
use Microsoft\PhpParser\Node\Expression\Variable as ParserVariable;
use Microsoft\PhpParser\Node\NamespaceUseClause;
use Microsoft\PhpParser\Node\NumericLiteral;
use Microsoft\PhpParser\Node\Parameter;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\ReservedWord;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node\StringLiteral;
use Microsoft\PhpParser\Token;
use Phpactor\WorseReflection\Core\Logger;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\Type;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\TernaryExpression;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\ClassLike;

class SymbolInformationResolver
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
     * @var Logger
     */
    private $logger;

    /**
     * @var SymbolFactory
     */
    private $symbolFactory;

    public function __construct(Reflector $reflector, Logger $logger, SymbolFactory $symbolFactory = null)
    {
        $this->reflector = $reflector;
        $this->logger = $logger;
        $this->symbolFactory = $symbolFactory ?: new SymbolFactory();
        $this->memberTypeResolver = new MemberTypeResolver($reflector, $logger, $this->symbolFactory);
    }

    /**
     * @param Node $node
     */
    public function resolveNode(Frame $frame, $node): SymbolInformation
    {
        if (false === $node instanceof Node) {
            $this->logger->warning(sprintf('Non-node class passed to resolveNode, got "%s"', get_class($node)));
            return SymbolInformation::none();
        }

        // jump to the container for SubscriptExpression (array access)
        // TODO: this is strange and proably wrong.
        if ($node->getParent() instanceof SubscriptExpression) {
            return $this->resolveNode($frame, $node->getParent());
        }

        return $this->_resolveNode($frame, $node);
    }

    private function _resolveNode(Frame $frame, Node $node): SymbolInformation
    {
        $this->logger->debug(sprintf('Resolving: %s', get_class($node)));
        if ($node instanceof QualifiedName) {
            return $this->symbolFactory->information($node, [
                'type' => $this->resolveQualifiedName($node),
                'symbol_type' => Symbol::CLASS_,
            ]);
        }

        if ($node instanceof Parameter) {
            return $this->resolveParameter($frame, $node);
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

        if ($node instanceof ClassDeclaration || $node instanceof InterfaceDeclaration) {
            return $this->symbolFactory->information($node, [ 'type' => Type::fromString($node->getNamespacedName()) ]);
        }

        if ($node instanceof ObjectCreationExpression) {
            return $this->resolveObjectCreationExpression($frame, $node);
        }

        if ($node instanceof SubscriptExpression) {
            $variableValue = $this->_resolveNode($frame, $node->postfixExpression);
            return $this->resolveAccessExpression($frame, $variableValue, $node->accessExpression);
        }

        if ($node instanceof StringLiteral) {
            return $this->symbolFactory->information($node, [ 'type' => Type::string(), 'value' => (string) $node->getStringContentsText()]);
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

        $this->logger->warning(sprintf(
            'Did not know how to resolve node of type "%s" with text "%s"',
            get_class($node),
            $node->getText()
        ));

        return SymbolInformation::none();
    }

    private function resolveVariable(Frame $frame, ParserVariable $node)
    {
        $name = $node->getText();
        $offset = $node->getFullStart();
        $variables = $frame->locals()->lessThanOrEqualTo($offset)->byName($name);

        if (0 === $variables->count()) {
            return SymbolInformation::none();
        }

        return $variables->last()->symbolInformation();
    }

    private function resolveMemberAccessExpression(Frame $frame, MemberAccessExpression $node): SymbolInformation
    {
        $class = $this->_resolveNode($frame, $node->dereferencableExpression);

        return $this->_valueFromMemberAccess($frame, $class->type(), $node);
    }

    private function resolveCallExpression(Frame $frame, CallExpression $node): SymbolInformation
    {
        $resolvableNode = $node->callableExpression;
        return $this->_resolveNode($frame, $resolvableNode);
    }

    public function resolveQualifiedName(Node $node, string $name = null): Type
    {
        $name = $name ?: $node->getText();

        if (substr($name, 0, 1) === '\\') {
            return Type::fromString($name);
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

        $value = null;
        if ($node->default) {
            $value = $this->_resolveNode($frame, $node->default)->value();
        }

        return $this->symbolFactory->information($node, [
            'type' => $type,
            'token' => $node->variableName,
            'value' => $value,
            'symbol_type' => Symbol::VARIABLE,
        ]);
    }

    private function resolveNumericLiteral(Node $node)
    {
        // note hack to cast to either an int or a float
        $value = $node->getText() + 0;

        return $this->symbolFactory->information($node, [ 'type' => is_float($value) ? Type::float() : Type::int(), 'value' => $value ]);
    }

    private function resolveReservedWord(Node $node)
    {
        if ('null' === $node->getText()) {
            return $this->symbolFactory->information($node, [ 'type' => Type::null(), 'value' => null ]);
        }

        if ('false' === $node->getText()) {
            return $this->symbolFactory->information($node, [ 'type' => Type::bool(), 'value' => false ]);
        }

        if ('true' === $node->getText()) {
            return $this->symbolFactory->information($node, [ 'type' => Type::bool(), 'value' => true ]);
        }

        $this->logger->warning(sprintf('Could not resolve reserved word "%s"', $node->getText()));

        // TODO: Not tested
        return SymbolInformation::none();
    }

    private function resolveArrayCreationExpression(Frame $frame, Node $node)
    {
        $array  = [];

        if (null === $node->arrayElements) {
            return $this->symbolFactory->information($node, [ 'type' => Type::array(), 'value' => [] ]);
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

        return $this->symbolFactory->information($node, [ 'type' => Type::array(), 'value' => $array ]);
    }

    private function resolveAccessExpression(Frame $frame, SymbolInformation $subject, Node $node): SymbolInformation
    {
        // TODO: test me
        if ($subject->value() == SymbolInformation::none()) {
            return SymbolInformation::none();
        }

        if ($subject->type() != Type::array()) {
            $this->logger->warning(sprintf(
                'Not resolving access expression of type "%s"',
                (string) $subject->type()
            ));
            return SymbolInformation::none();
        }

        $subjectValue = $subject->value();

        if (false === is_array($subjectValue )) {
            $this->logger->debug(sprintf(
                'Array value for symbol "%s" is not an array, is a "%s"',
                (string) $subject->symbol(),
                gettype($subjectValue)
            ));
            return SymbolInformation::none();
        }

        if ($node instanceof StringLiteral) {
            $string = $this->_resolveNode($frame, $node);

            if (array_key_exists($string->value(), $subjectValue)) {
                $value = $subjectValue[$string->value()];
                return $this->symbolFactory->information($node, [ 'type' => Type::fromValue($value), 'value' => $value ]);
            }
        }

        $this->logger->warning(sprintf(
            'Did not resolve access expression for node type "%s"',
            get_class($node)
        ));

        return SymbolInformation::none();
    }

    private function resolveScopedPropertyAccessExpression(Frame $frame, ScopedPropertyAccessExpression $node)
    {
        $name = $node->scopeResolutionQualifier->getText();
        $parent = $this->resolveQualifiedName($node, $name);

        return $this->_valueFromMemberAccess($frame, $parent, $node);
    }

    private function resolveObjectCreationExpression(Frame $frame, $node)
    {
        if (false === $node->classTypeDesignator instanceof Node) {
            $this->logger->warning(sprintf('Could not create object from "%s"', get_class($node)));
            return SymbolInformation::none();
        }

        return $this->_resolveNode($frame, $node->classTypeDesignator);
    }

    private function resolveTernaryExpression(Frame $frame, TernaryExpression $node)
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

        return SymbolInformation::none();
    }

    private function resolveMethodDeclaration(Frame $frame, MethodDeclaration $methodDeclaration)
    {
        $classNode = $methodDeclaration->getFirstAncestor(ClassLike::class);
        $classSymbolInformation = $this->_resolveNode($frame, $classNode);
        return $this->symbolFactory->information(
            $methodDeclaration, [
                'token' => $methodDeclaration->name,
                'class_type' => $classSymbolInformation->type(),
                'symbol_type' => Symbol::METHOD,
            ]
        );
    }

    private function _valueFromMemberAccess(Frame $frame, Type $classType, Node $node)
    {
        $memberName = $node->memberName->getText($node->getFileContents());
        $memberType = $node->getParent() instanceof CallExpression ? 'method' : 'property';

        if ($node->memberName instanceof Node) {
            $memberNameInfo = $this->_resolveNode($frame, $node->memberName);
            if (is_string($memberNameInfo->value())) {
                $memberName = $memberNameInfo->value();
            }
        }

        if ('property' === $memberType && $node instanceof ScopedPropertyAccessExpression && substr($memberName, 0, 1) !== '$') {
            $memberType = 'constant';
        }

        $information = $this->symbolFactory->information(
            $node,
            [
                'symbol_type' => $memberType,
                'token' => $node->memberName instanceof Token ? $node->memberName : null,
            ]
        );

        // if the classType is a call expression, then this is a method call
        $info = $this->memberTypeResolver->{$memberType . 'Type'}($classType, $information, $memberName);

        $this->logger->debug(sprintf(
            'Resolved type "%s" for %s "%s" of class "%s"',
            (string) $info->type(),
            $memberType,
            $memberName,
            (string) $classType
        ));

        return $info;

    }
}
