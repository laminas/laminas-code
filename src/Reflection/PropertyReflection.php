<?php

namespace Laminas\Code\Reflection;

use ReflectionProperty as PhpReflectionProperty;

/**
 * @todo       implement line numbers
 */
class PropertyReflection extends PhpReflectionProperty implements ReflectionInterface
{
    /**
     * Get declaring class reflection object
     *
     * @return ClassReflection
     */
    public function getDeclaringClass()
    {
        $phpReflection     = parent::getDeclaringClass();
        $laminasReflection = new ClassReflection($phpReflection->getName());
        unset($phpReflection);

        return $laminasReflection;
    }

    /**
     * Get DocBlock comment
     *
     * @return string|false False if no DocBlock defined
     */
    public function getDocComment()
    {
        return parent::getDocComment();
    }

    /**
     * @return false|DocBlockReflection
     */
    public function getDocBlock()
    {
        if (! ($docComment = $this->getDocComment())) {
            return false;
        }

        return new DocBlockReflection($docComment);
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->__toString();
    }
}
