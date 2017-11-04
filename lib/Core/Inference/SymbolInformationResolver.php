<?php

namespace Phpactor\WorseReflection\Core\Inference;

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
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\ConstElement;

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
        $this->logger = $logger;
        $this->symbolFactory = $symbolFactory ?: new SymbolFactory();
        $this->memberTypeResolver = new MemberTypeResolver($reflector, $logger);
    }

    /**
    ace
     */
    public function resolveNode(Frame $frame, $node): SymbolInformation
    {
        return $this->_resolveNode($frame, $node);
    }

    /**
     * Internal interface
     */
    public function _resolveNode(Frame $frame, $node): SymbolInformation
    {
        if (false === $node instanceof Node) {
            $this->logger->warning(sprintf('Non-node class passed to resolveNode, got "%s"', get_class($node)));
            return SymbolInformation::none();
        }

        return $this->__resolveNode($frame, $node);
    }

    private function __resolveNode(Frame $frame, Node $node): SymbolInformation
    {
        $this->logger->debug(sprintf('Resolving: %s', get_class($node)));

        /** @var QualifiedName $node */
        if ($node instanceof QualifiedName) {
            return $this->symbolFactory->information(
                $node->getText(),
                $node->getStart(),
                $node->getEndPosition(),
                [
                    'type' => $this->resolveQualifiedNameType($node),
                    'symbol_type' => Symbol::CLASS_
                ]
            );
        }

        /** @var ConstElement $node */
        if ($node instanceof ConstElement) {
            return $this->symbolFactory->information(
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

        /** @var ClassDeclaration $node */
        if ($node instanceof ClassLike) {
            return $this->symbolFactory->information(
                $node->name->getText($node->getFileContents()),
                $node->name->getEndPosition(),
                $node->name->getStartPosition(),
                [
                    'symbol_type' => Symbol::CLASS_,
                    'type' => Type::fromString($node->getNamespacedName())
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
            return $this->symbolFactory->information(
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

        $this->logger->warning(sprintf(
            'Did not know how to resolve node of type "%s" with text "%s"',
            get_class($node),
            $node->getText()
        ));

        return SymbolInformation::none();
    }

    private function resolveVariable(Frame $frame, ParserVariable $node)
    {
        if ($node->getFirstAncestor(PropertyDeclaration::class)) {
            return $this->resolvePropertyVariable($node);
        }

        $name = $node->getText();
        $name = ltrim($name, '$');
        $offset = $node->getFullStart();
        $variables = $frame->locals()->lessThanOrEqualTo($offset)->byName($name);

        if (0 === $variables->count()) {
            return $this->symbolFactory->information(
                $node->name->getText($node->getFileContents()),
                $node->getStart(),
                $node->getEndPosition(),
                [
                    'symbol_type' => Symbol::VARIABLE
                ]
            );
        }

        return $variables->last()->symbolInformation();
    }

    private function resolvePropertyVariable(ParserVariable $node)
    {
        $info = $this->symbolFactory->information(
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

    public function resolveQualifiedNameType(Node $node, string $name = null): Type
    {
        $name = $name ?: $node->getText();

        if (!$node instanceof ScopedPropertyAccessExpression && $node->parent instanceof CallExpression) {
            return Type::unknown();
        }

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
            $type = $this->resolveQualifiedNameType($node->typeDeclaration);
        }
        
        if ($typeDeclaration instanceof Token) {
            $type = Type::fromString($typeDeclaration->getText($node->getFileContents()));
        }

        $value = null;
        if ($node->default) {
            $value = $this->_resolveNode($frame, $node->default)->value();
        }

        return $this->symbolFactory->information(
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

    private function resolveNumericLiteral(NumericLiteral $node)
    {
        // note hack to cast to either an int or a float
        $value = $node->getText() + 0;

        return $this->symbolFactory->information(
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

    private function resolveReservedWord(Node $node)
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

        if (null === $symbolType) {
            $this->logger->warning(sprintf('Could not resolve reserved word "%s"', $node->getText()));
            $symbolType = Symbol::UNKNOWN;
        }

        $information = $this->symbolFactory->information(
            $node->getText(),
            $node->getStart(),
            $node->getEndPosition(),
            [
                'value' => $value,
                'type' => $type,
                'symbol_type' => $symbolType,
                'container_type' => $containerType,
            ]
        );

        if (null === $type) {
            $this->logger->warning(sprintf('Could not resolve reserved word "%s"', $node->getText()));
        }

        return $information;
    }

    private function resolveArrayCreationExpression(Frame $frame, ArrayCreationExpression $node)
    {
        $array  = [];

        if (null === $node->arrayElements) {
            return $this->symbolFactory->information(
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

        return $this->symbolFactory->information(
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
        SymbolInformation $subject,
        SubscriptExpression $node = null
    ): SymbolInformation
    {
        if (null === $node->accessExpression) {
            $this->logger->warning(sprintf(
                'Subscript expression "%s" is incomplete',
                (string) $node->getText()
            ));
        }

        $node = $node->accessExpression;
        // TODO: test me
        if ($subject->value() == SymbolInformation::none()) {
            return SymbolInformation::none();
        }

        if ($subject->type() != Type::array()) {
            $this->logger->warning(sprintf(
                'Not resolving subscript expression of type "%s"',
                (string) $subject->type()
            ));
            return SymbolInformation::none();
        }

        $subjectValue = $subject->value();

        if (false === is_array($subjectValue)) {
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
                return $string->withValue($value);
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
        $parent = $this->resolveQualifiedNameType($node, $name);

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

    private function resolveMethodDeclaration(Frame $frame, MethodDeclaration $node)
    {
        $classNode = $node->getFirstAncestor(ClassLike::class);
        $classSymbolInformation = $this->_resolveNode($frame, $classNode);

        return $this->symbolFactory->information(
            $node->name->getText($node->getFileContents()),
            $node->name->getStartPosition(),
            $node->name->getEndPosition(),
            [
                'container_type' => $classSymbolInformation->type(),
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
            $memberName,
            $node->getStart(),
            $node->getEndPosition(),
            [
                'symbol_type' => $memberType,
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

    private function classTypeFromNode(Node $node)
    {
        $classNode = $node->getFirstAncestor(ClassLike::class);

        if (null === $classNode) {
            return;
        }

        return Type::fromString($classNode->getNamespacedName());
    }
}
