<?php

namespace Laminas\Code\Reflection;

use Laminas\Code\Reflection\DocBlock\Tag\ParamTag;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use ReturnTypeWillChange;

use function method_exists;

class ParameterReflection extends ReflectionParameter implements ReflectionInterface
{
    /** @var bool */
    protected $isFromMethod = false;

    /**
     * Get declaring class reflection object
     *
     * @return ClassReflection
     */
    #[ReturnTypeWillChange]
    public function getDeclaringClass()
    {
        $phpReflection     = parent::getDeclaringClass();
        $laminasReflection = new ClassReflection($phpReflection->getName());
        unset($phpReflection);

        return $laminasReflection;
    }

    /**
     * Get class reflection object
     *
     * @return null|ClassReflection
     */
    #[ReturnTypeWillChange]
    public function getClass()
    {
        $phpReflectionType = parent::getType();
        if ($phpReflectionType === null) {
            return null;
        }

        $laminasReflection = new ClassReflection($phpReflectionType->getName());
        unset($phpReflectionType);

        return $laminasReflection;
    }

    /**
     * Get declaring function reflection object
     *
     * @return FunctionReflection|MethodReflection
     */
    #[ReturnTypeWillChange]
    public function getDeclaringFunction()
    {
        $phpReflection = parent::getDeclaringFunction();
        if ($phpReflection instanceof ReflectionMethod) {
            $laminasReflection = new MethodReflection($this->getDeclaringClass()->getName(), $phpReflection->getName());
        } else {
            $laminasReflection = new FunctionReflection($phpReflection->getName());
        }
        unset($phpReflection);

        return $laminasReflection;
    }

    /**
     * Get parameter type
     *
     * @return string|null
     */
    public function detectType()
    {
        if (
            method_exists($this, 'getType')
            && null !== ($type = $this->getType())
            && $type->isBuiltin()
        ) {
            return $type->getName();
        }

        if (null !== $type && $type->getName() === 'self') {
            return $this->getDeclaringClass()->getName();
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

    /**
     * @return string
     */
    public function __toString()
    {
        return parent::__toString();
    }

    /** @psalm-pure */
    public function isPromoted(): bool
    {
        if (! method_exists(parent::class, 'isPromoted')) {
            return false;
        }

        return (bool) parent::isPromoted();
    }

    public function isPublicPromoted(): bool
    {
        return $this->isPromoted()
            && $this->getDeclaringClass()
                ->getProperty($this->getName())
                ->getModifiers()
            & ReflectionProperty::IS_PUBLIC;
    }

    public function isProtectedPromoted(): bool
    {
        return $this->isPromoted()
            && $this->getDeclaringClass()
                ->getProperty($this->getName())
                ->getModifiers()
            & ReflectionProperty::IS_PROTECTED;
    }

    public function isPrivatePromoted(): bool
    {
        return $this->isPromoted()
            && $this->getDeclaringClass()
                ->getProperty($this->getName())
                ->getModifiers()
            & ReflectionProperty::IS_PRIVATE;
    }
}
