<?php

namespace Phpactor\WorseReflection\Core;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\Statement\NamespaceDefinition;
use Phpactor\WorseReflection\Core\Logger\ArrayLogger;
use Microsoft\PhpParser\ClassLike;

/**
 * TODO: Remove this class.
 */
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

        if (!preg_match(sprintf('{@%s (\$?[\w+\\\]+)}', $tag), $comment, $matches)) {
            return Type::unknown();
        }
        
        return $this->typeFromString($node, $matches[1]);
    }

    private function typeFromString(Node $node, string $typeString)
    {
        if (in_array($typeString, ['$this', 'static'])) {
            $classDeclaration = $node->getFirstAncestor(ClassLike::class);

            if (null === $classDeclaration) {
                return Type::unknown();
            }

            return Type::fromString((string) $classDeclaration->getNamespacedName());
        }

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
}
