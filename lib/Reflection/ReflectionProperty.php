<?php

namespace DTL\WorseReflection\Reflection;

use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use DTL\WorseReflection\Visibility;

class ReflectionProperty
{
    private $propertyNode;
    private $propertyPropertyNode;

    public function __construct(Property $propertyNode, PropertyProperty $propertyPropertyNode)
    {
        $this->propertyPropertyNode = $propertyPropertyNode;
        $this->propertyNode = $propertyNode;
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
}
