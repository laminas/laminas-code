<?php

namespace Laminas\Code\Reflection;

use Laminas\Code\Reflection\DocBlock\Tag\ParamTag;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReturnTypeWillChange;

use function assert;

/** @psalm-immutable */
class ParameterReflection extends ReflectionParameter implements ReflectionInterface
{
    /** @var bool */
    protected $isFromMethod = false;

    /**
     * Get declaring class reflection object
     *
     * @return ClassReflection|null
     */
    #[ReturnTypeWillChange]
    public function getDeclaringClass()
    {
        $reflection = parent::getDeclaringClass();

        if (! $reflection) {
            return null;
        }

        return new ClassReflection($reflection->getName());
    }

    /**
     * Get class reflection object
     *
     * @return null|ClassReflection
     */
    #[ReturnTypeWillChange]
    public function getClass()
    {
        $type = parent::getType();

        if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
            return null;
        }

        return new ClassReflection($type->getName());
    }

    /**
     * Get declaring function reflection object
     *
     * @return FunctionReflection|MethodReflection
     */
    #[ReturnTypeWillChange]
    public function getDeclaringFunction()
    {
        $function = parent::getDeclaringFunction();

        if ($function instanceof ReflectionMethod) {
            return new MethodReflection($function->getDeclaringClass()->getName(), $function->getName());
        }

        return new FunctionReflection($function->getName());
    }

    /**
     * Get parameter type
     *
     * @deprecated this method is unreliable, and should not be used: it will be removed in the next major release.
     *             It may crash on parameters with union types, and will return relative types, instead of
     *             FQN references
     *
     * @return string|null
     */
    public function detectType()
    {
        if (
            null !== ($type = $this->getType())
            && $type->isBuiltin()
        ) {
            return $type->getName();
        }

        if (null !== $type && $type->getName() === 'self') {
            $declaringClass = $this->getDeclaringClass();

            assert($declaringClass !== null, 'A parameter called `self` can only exist on a class');

            return $declaringClass->getName();
        }

        if (($class = $this->getClass()) instanceof ReflectionClass) {
            return $class->getName();
        }

        $docBlock = $this->getDeclaringFunction()->getDocBlock();

        if (! $docBlock instanceof DocBlockReflection) {
            return null;
        }

        /** @var ParamTag[] $params */
        $params       = $docBlock->getTags('param');
        $paramTag     = $params[$this->getPosition()] ?? null;
        $variableName = '$' . $this->getName();

        if ($paramTag && ('' === $paramTag->getVariableName() || $variableName === $paramTag->getVariableName())) {
            return $paramTag->getTypes()[0] ?? '';
        }

        foreach ($params as $param) {
            if ($param->getVariableName() === $variableName) {
                return $param->getTypes()[0] ?? '';
            }
        }

        return null;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return parent::__toString();
    }

    public function isPublicPromoted(): bool
    {
        $property = $this->promotedProperty();

        if ($property === null) {
            return false;
        }

        return (bool) ($property->getModifiers() & ReflectionProperty::IS_PUBLIC);
    }

    public function isProtectedPromoted(): bool
    {
        $property = $this->promotedProperty();

        if ($property === null) {
            return false;
        }

        return (bool) ($property->getModifiers() & ReflectionProperty::IS_PROTECTED);
    }

    public function isPrivatePromoted(): bool
    {
        $property = $this->promotedProperty();

        if ($property === null) {
            return false;
        }

        return (bool) ($property->getModifiers() & ReflectionProperty::IS_PRIVATE);
    }

    private function promotedProperty(): ?ReflectionProperty
    {
        if (! $this->isPromoted()) {
            return null;
        }

        $declaringClass = $this->getDeclaringClass();

        assert($declaringClass !== null, 'Promoted properties are always part of a class');

        return $declaringClass->getProperty($this->getName());
    }
}
