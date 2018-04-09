<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\ClassLike;

use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\NamespacedNameInterface;
use RuntimeException;
use Microsoft\PhpParser\TokenKind;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Microsoft\PhpParser\Node\ConstElement;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\Statement\ConstDeclaration;
use Microsoft\PhpParser\Node\ClassConstDeclaration;

abstract class AbstractReflectionClassMember extends AbstractReflectedNode
{
    public function declaringClass(): ReflectionClassLike
    {
        $classDeclaration = $this->node()->getFirstAncestor(ClassLike::class);

        assert($classDeclaration instanceof NamespacedNameInterface);

        $class = $classDeclaration->getNamespacedName();

        if (null === $class) {
            throw new \InvalidArgumentException(sprintf(
                'Could not locate class-like ancestor node for member "%s"',
                $this->name()
            ));
        }

        return $this->serviceLocator()->reflector()->reflectClassLike(ClassName::fromString($class));
    }

    public function frame(): Frame
    {
        return $this->serviceLocator()->frameBuilder()->build($this->node());
    }

    public function docblock(): DocBlock
    {
        return $this->serviceLocator()->docblockFactory()->create($this->node()->getLeadingCommentAndWhitespaceText());
    }

    public function visibility(): Visibility
    {
        $node = $this->node();
        assert($node instanceof PropertyDeclaration || $node instanceof ClassConstDeclaration || $node instanceof MethodDeclaration);
        foreach ($node->modifiers as $token) {
            if ($token->kind === TokenKind::PrivateKeyword) {
                return Visibility::private();
            }

            if ($token->kind === TokenKind::ProtectedKeyword) {
                return Visibility::protected();
            }
        }

        return Visibility::public();
    }

    abstract protected function serviceLocator(): ServiceLocator;

    abstract protected function name(): string;


}
