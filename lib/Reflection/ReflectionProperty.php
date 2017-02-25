<?php

namespace DTL\WorseReflection\Reflection;

use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use DTL\WorseReflection\Visibility;
use DTL\WorseReflection\Type;
use DTL\WorseReflection\SourceContext;
use DTL\WorseReflection\Util\DocCommentParser;

class ReflectionProperty
{
    private $propertyNode;
    private $propertyPropertyNode;
    private $sourceContext;

    public function __construct(SourceContext $sourceContext, Property $propertyNode, PropertyProperty $propertyPropertyNode)
    {
        $this->propertyPropertyNode = $propertyPropertyNode;
        $this->propertyNode = $propertyNode;
        $this->sourceContext = $sourceContext;
    }

    public function getName() 
    {
        return (string) $this->propertyPropertyNode->name;
    }
    
    public function getVisibility()
    {
        if ($this->propertyNode->isProtected()) {
            return Visibility::protected();
        }

        if ($this->propertyNode->isPrivate()) {
            return Visibility::private();
        }

        return Visibility::public();
    }

    public function isStatic()
    {
        return $this->propertyNode->isStatic();
    }

    public function getType(): Type
    {
        $comment = $this->propertyNode->getDocComment();

        if (!$comment) {
            return Type::unknown();
        }

        if ($varString = DocCommentParser::parseVarDoc($comment)) {
            return Type::fromString($this->sourceContext, $varString);
        }

        return Type::unknown();
    }

    public function getDocComment(): string
    {
        return DocCommentParser::parseProse($this->propertyNode->getDocComment());
    }
}
