<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolvers;

use Phpactor\WorseReflection\Core\Inference\SymbolContext;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\AnonymousFunctionCreationExpression;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\SymbolContextResolver;
use Phpactor\WorseReflection\Core\Inference\SymbolFactory;
use Phpactor\WorseReflection\Core\Inference\FullyQualifiedNameResolver;
use Phpactor\WorseReflection\Reflector;
use Microsoft\PhpParser\Node\Parameter;
use Phpactor\WorseReflection\Core\Type;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Token;

class ParameterResolver
{
    /**
     * @var SymbolFactory
     */
    private $symbolFactory;

    /**
     * @var FullyQualifiedNameResolver
     */
    private $nameResolver;

    /**
     * @var Reflector
     */
    private $reflector;

    public function __construct(
        SymbolFactory $symbolFactory,
        FullyQualifiedNameResolver $nameResolver,
        Reflector $reflector
    )
    {
        $this->symbolFactory = $symbolFactory;
        $this->nameResolver = $nameResolver;
        $this->reflector = $reflector;
    }

    public function resolve(SymbolContextResolver $resolver, Frame $frame, Node $node): SymbolContext
    {
        /** @var MethodDeclaration $method */
        $method = $node->getFirstAncestor(
            AnonymousFunctionCreationExpression::class,
            MethodDeclaration::class
        );

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
            $value = $resolver->resolveNode($frame, $node->default)->value();
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

    private function resolveParameterFromReflection(
        Frame $frame,
        MethodDeclaration $method,
        Parameter $node
    ): SymbolContext
    {
        /** @var ClassDeclaration|TraitDeclaration|InterfaceDeclaration $class  */
        $class = $node->getFirstAncestor(
            ClassDeclaration::class,
            InterfaceDeclaration::class,
            TraitDeclaration::class
        );

        if (null === $class) {
            return SymbolContext::none()
                ->withIssue(sprintf(
                'Cannot find class context "%s" for parameter',
                $node->getName()
            ));
        }

        /** @var ReflectionClass|ReflectionIntreface $reflectionClass */
        $reflectionClass = $this->reflector->reflectClassLike($class->getNamespacedName()->__toString());
        $reflectionMethod = $reflectionClass->methods()->get($method->getName());

        if (!$reflectionMethod->parameters()->has($node->getName())) {
            return SymbolContext::none()
                ->withIssue(sprintf(
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
}
