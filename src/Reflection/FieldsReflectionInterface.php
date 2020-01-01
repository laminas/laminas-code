<?php

namespace Laminas\Code\Reflection;

interface FieldsReflectionInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * Get declaring class reflection object
     *
     * @return ClassReflection
     */
    public function getDeclaringClass();

    /**
     * Get DocBlock comment
     *
     * @return string|false False if no DocBlock defined
     */
    public function getDocComment();

    /**
     * @return false|DocBlockReflection
     */
    public function getDocBlock();

    /**
     * @return boolean
     */
    public function isStatic();

    /**
     * @return boolean
     */
    public function isPrivate();

    /**
     * @return boolean
     */
    public function isProtected();
}