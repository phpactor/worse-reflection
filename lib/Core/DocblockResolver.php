<?php

namespace Phpactor\WorseReflection\Core;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\AbstractReflectionClass;
use Microsoft\PhpParser\Node\Statement\NamespaceDefinition;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Phpactor\WorseReflection\Core\Logger;
use Phpactor\WorseReflection\Core\Logger\ArrayLogger;

class DocblockResolver
{
    /**
     * @var Logger
     */
    private $logger;

    public function __construct(Logger $logger = null)
    {
        $this->logger = $logger ?: new ArrayLogger();
    }

    public function methodReturnTypeFromNodeDocblock(AbstractReflectionClass $class, MethodDeclaration $node)
    {
        $methodName = $node->name->getText($node->getFileContents());
        if (preg_match('{@method ([\w\\\]+) ' . $methodName . '\(}', (string) $class->docblock(), $matches)) {
            return $this->typeFromString($node, $matches[1]);
        }

        if (Type::unknown() != $type = $this->typeFromNode($node, 'return')) {
            return $type;
        }

        if (preg_match('#inheritdoc#i', $node->getLeadingCommentAndWhitespaceText())) {
            if ($class->isTrait()) {
                // TODO: Warn about inherit block on trait
                return Type::unknown();
            }

            $parents = $this->classParents($class);

            foreach ($parents as $parent) {
                $parentMethods = $parent->methods();
                if ($parentMethods->has($node->getName())) {
                    return $parentMethods->get($node->getName())->inferredReturnType();
                }
            }
        }

        return Type::unknown();
    }

    public function propertyType(PropertyDeclaration $node)
    {
        return $this->typeFromNode($node, 'var');
    }

    public function nodeType(Node $node)
    {
        return $this->typeFromNode($node, 'var');
    }

    private function typeFromNode(Node $node, string $tag)
    {
        $comment = $node->getLeadingCommentAndWhitespaceText();

        if (!preg_match(sprintf('{@%s ([\w+\\\]+)}', $tag), $comment, $matches)) {
            return Type::unknown();
        }
        
        return $this->typeFromString($node, $matches[1]);
    }

    private function typeFromString(Node $node, string $typeString)
    {
        if (substr($typeString, 0, 1) == '\\') {
            return Type::fromString($typeString);
        }

        $typeString = trim($typeString, '\\');

        $type = Type::fromString($typeString);

        if ($type->isPrimitive()) {
            return $type;
        }

        $parts = explode('\\', $typeString);

        $importTable = $node->getImportTablesForCurrentScope()[0];
        $firstPart = array_shift($parts);

        if (isset($importTable[$firstPart])) {
            return Type::fromString($importTable[$firstPart].'\\'.implode('\\', $parts));
        }

        $namespace = $node->getRoot()->getFirstChildNode(NamespaceDefinition::class);

        if (null === $namespace) {
            return $type;
        }

        return Type::fromArray([(string) $namespace->name, $typeString]);
    }

    private function classParents(AbstractReflectionClass $class)
    {
        if ($class->isClass()) {
            /** @var ReflectionClass $class */
            $parent = $class->parent();

            if (null === $parent) {
                $this->logger->warning(sprintf(
                    'inheritdoc used on class "%s", but class has no parent',
                    $class->name()->full()
                ));
                return [];
            }

            return [ $parent ];
        }

        if ($class->isInterface()) {
            /** @var ReflectionInterface $class */
            return $class->parents();
        }

        throw new InvalidArgumentException(sprintf(
            'Do not know how to get parents for "%s"',
            get_class($class)
        ));
    }
}
